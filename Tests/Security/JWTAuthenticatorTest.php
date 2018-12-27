<?php

namespace Auth0\JWTAuthBundle\Tests\Security;

use Auth0\JWTAuthBundle\Security\Auth0Service;
use Auth0\JWTAuthBundle\Security\JWTAuthenticator;

class JWTAuthenticatorTest extends \PHPUnit_Framework_TestCase
{
    public function testTokenCreation()
    {
        $mockAuth0 = $this->getMockBuilder('Auth0\JWTAuthBundle\Security\Auth0Service')
            ->disableOriginalConstructor()
            ->getMock();

        $mockAuth0->expects($this->once())
            ->method('decodeJWT')
            ->will($this->returnValue(new \stdClass()));

        $authenticator = new JWTAuthenticator($mockAuth0);
        $providerKey = 'providerKey';

        //generated with http://jwt.io/
        $JWT = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwczovL2RvbWFpbi5jb20vIiwic3ViIjoiYXV0aDB8MDAwMDAwMDAwMDAwMDAwMDAwMDAwMCIsImF1ZCI6ImNsaWVudF9pZCIsImV4cCI6MTQyMjQ0MDI3MSwiaWF0IjoxNDIyNDA0MjcxfQ.xSuCAetwfHpCWhE_5NqTrwHq0eQ7CVffQwgSqTHwwrY';

        $request = $this->getMock('Symfony\Component\HttpFoundation\Request');
        $request->headers = $this->getMock('Symfony\Component\HttpFoundation\ParameterBag');

        $request->headers
            ->expects($this->atLeastOnce())
            ->method('get')
            ->with('Authorization')
            ->will($this->returnValue($JWT));

        $token = $authenticator->createToken($request, $providerKey);

        $this->assertEquals('anon.',$token->GetUser());
        $this->assertEquals($providerKey, $token->getProviderKey());
        $this->assertEquals($providerKey, $token->getProviderKey());
    }

    public function testNoAuthorization()
    {
        $mockAuth0 = $this->getMockBuilder('Auth0\JWTAuthBundle\Security\Auth0Service')
            ->disableOriginalConstructor()
            ->getMock();

        $authenticator = new JWTAuthenticator($mockAuth0);
        $providerKey = 'providerKey';

        $request = $this->getMock('Symfony\Component\HttpFoundation\Request');
        $request->headers = $this->getMock('Symfony\Component\HttpFoundation\ParameterBag');

        $request->headers
            ->expects($this->once())
            ->method('get')
            ->with('Authorization')
            ->will($this->returnValue(NULL));

        $token = $authenticator->createToken($request, $providerKey);

        $this->assertInstanceOf('Symfony\Component\Security\Core\Authentication\Token\PreAuthenticatedToken', $token);

        $this->assertEquals($providerKey, $token->getProviderKey());
        $this->assertNull($token->getCredentials());
    }

}
