<?php


namespace Auth0\JWTAuthBundle\Security;

use Doctrine\Common\Proxy\Exception\InvalidArgumentException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Authentication\Token\PreAuthenticatedToken;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Http\Authentication\SimplePreAuthenticatorInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface;

use Auth0\JWTAuthBundle\Security\Core\JWTUserProviderInterface;

class JWTAuthenticator implements SimplePreAuthenticatorInterface,AuthenticationFailureHandlerInterface
{
    use ContainerAwareTrait;
    protected $auth0Service;

    public function __construct(Auth0Service $auth0Service)
    {
        $this->auth0Service = $auth0Service;
    }

    public function createToken(Request $request, $providerKey)
    {
        // look for an authorization header
        $authorizationHeader = $request->headers->get('Authorization');

        if ($authorizationHeader == null) {
            return new PreAuthenticatedToken(
                'anon.',
                null,
                $providerKey
            );
        }

        // extract the JWT
        $authToken = str_replace('Bearer ', '', $authorizationHeader);

        // decode and validate the JWT
        try {
            $token = $this->auth0Service->decodeJWT($authToken);
            $token->token = $authToken;
        } catch(\UnexpectedValueException $ex) {
            throw new BadCredentialsException('Invalid token');
        }

        return new PreAuthenticatedToken(
            'anon.',
            $token,
            $providerKey
        );
    }

    /**
     * @param TokenInterface           $token
     * @param JWTUserProviderInterface $userProvider
     * @param                          $providerKey
     *
     * @return PreAuthenticatedToken
     *
     * @throws \Symfony\Component\Security\Core\Exception\AuthenticationException
     */
    public function authenticateToken(TokenInterface $token, UserProviderInterface $userProvider, $providerKey)
    {
        // The user provider should implement JWTUserProviderInterface
        if (!$userProvider instanceof JWTUserProviderInterface) {
            throw new InvalidArgumentException('Argument must implement interface Auth0\JWTAuthBundle\Security\Core\JWTUserProviderInterface');
        }

        if ($token->getCredentials() === null) {
            $user = $userProvider->getAnonymousUser();
        } else {
            // Get the user for the injected UserProvider
            $user = $userProvider->loadUserByJWT($token->getCredentials());

            if (!$user) {
                throw new AuthenticationException(sprintf('Invalid JWT.'));
            }
        }

        return new PreAuthenticatedToken(
            $user,
            $token,
            $providerKey,
            $user->getRoles()
        );
    }

    public function supportsToken(TokenInterface $token, $providerKey)
    {
        return $token instanceof PreAuthenticatedToken && $token->getProviderKey() === $providerKey;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        return new Response("Authentication Failed: {$exception->getMessage()}", 403);
    }

}
