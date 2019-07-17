<?php

namespace Auth0\JWTAuthBundle\Security\Guard;

use Auth0\JWTAuthBundle\Security\Auth0Service;
use Auth0\JWTAuthBundle\Security\Core\JWTUserProviderInterface;
use Auth0\SDK\Exception\CoreException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\User;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;

/**
 * Handles authentication with JSON Web Tokens through the 'Authorization' request header.
 *
 * @author Niels Nijens <nijens.niels@gmail.com>
 */
class JwtGuardAuthenticator extends AbstractGuardAuthenticator
{
    /**
     * @var Auth0Service
     */
    private $authZeroService;

    /**
     * Constructs a new JwtGuardAuthenticator instance.
     *
     * @param Auth0Service $authZeroService
     */
    public function __construct(Auth0Service $authZeroService)
    {
        $this->authZeroService = $authZeroService;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(Request $request)
    {
        return $request->headers->has('Authorization');
    }

    /**
     * Retrieves the authentication credentials from the 'Authorization' request header.
     *
     * @param Request $request
     *
     * @return array|null
     */
    public function getCredentials(Request $request)
    {
        // Removes the 'Bearer ' part from the Authorization header value.
        $jwt = substr($request->headers->get('Authorization', ''), 7);
        if (empty($jwt)) {
            return null;
        }

        return array(
            'jwt' => $jwt,
        );
    }

    /**
     * Returns a user based on the information inside the JSON Web Token depending on the implementation
     * of the configured user provider.
     *
     * When the user provider does not implement the JWTUserProviderInterface it will attempt to load
     * the user by username with the 'sub' (subject) claim of the JSON Web Token.
     *
     * @param array                 $credentials
     * @param UserProviderInterface $userProvider
     *
     * @return UserInterface|null
     */
    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        try {
            $jwt = $this->authZeroService->decodeJWT($credentials['jwt']);
        } catch (CoreException $exception) {
            // Skip JWT verification exceptions here.
            // Verification will be done in checkCredentials().
            return new User('unknown', null, array());
        }

        if ($userProvider instanceof JWTUserProviderInterface) {
            return $userProvider->loadUserByJWT($jwt);
        }

        return $userProvider->loadUserByUsername($jwt->sub);
    }

    /**
     * Returns true when the provided JSON Web Token successfully decodes and validates.
     *
     * @param array         $credentials
     * @param UserInterface $user
     *
     * @return bool
     *
     * @throws AuthenticationException when decoding and/or validation of the JSON Web Token fails
     */
    public function checkCredentials($credentials, UserInterface $user)
    {
        try {
            $this->authZeroService->decodeJWT($credentials['jwt']);

            return true;
        } catch (CoreException $exception) {
            throw new AuthenticationException($exception->getMessage(), $exception->getCode(), $exception);
        }
    }

    /**
     * Returns nothing to continue the request when authenticated.
     *
     * @param Request        $request
     * @param TokenInterface $token
     * @param string         $providerKey
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        // Continue with request.
    }

    /**
     * Returns the 'Authentication failed' response.
     *
     * @param Request                 $request
     * @param AuthenticationException $exception
     *
     * @return JsonResponse
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        $responseBody = array(
            'message' => sprintf(
                'Authentication failed: %s.',
                rtrim($exception->getMessage(), '.')
            ),
        );

        return new JsonResponse($responseBody, Response::HTTP_FORBIDDEN);
    }

    /**
     * Returns a response that directs the user to authenticate.
     *
     * @param Request                 $request
     * @param AuthenticationException $authenticationException
     *
     * @return JsonResponse
     */
    public function start(Request $request, AuthenticationException $authenticationException = null)
    {
        $responseBody = array(
            'message' => 'Authentication required.',
        );

        return new JsonResponse($responseBody, Response::HTTP_UNAUTHORIZED);
    }

    /**
     * {@inheritdoc}
     */
    public function supportsRememberMe()
    {
        return false;
    }
}
