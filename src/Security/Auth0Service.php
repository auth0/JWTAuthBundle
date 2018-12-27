<?php

namespace Auth0\JWTAuthBundle\Security;

use Auth0\SDK\Auth0JWT;
use Auth0\SDK\Auth0Api;
use Symfony\Component\Security\Core\User\User;

/**
 * @author german
 *
 * Service that provides access to the Auth0 SDK and JWT validation
 */
class Auth0Service {

    private $client_id;
    private $client_secret;
    private $domain;
    private $oauth_client;

    public function __construct($client_id, $client_secret, $domain, $secret_base64_encoded)
    {
        $this->client_id = $client_id;
        $this->client_secret = $client_secret;
        $this->domain = $domain;
        $this->secret_base64_encoded = $secret_base64_encoded;
    }

    /**
     * Get the Auth0 User Profile based on the JWT (and validate it).
     *
     * @return User info as described in https://docs.auth0.com/user-profile
     */
    public function getUserProfileByA0UID($jwt, $a0UID)
    {
        $auth0Api = new Auth0Api($jwt, $this->domain);
        return $auth0Api->users->get($a0UID);
    }

    /**
     * Decodes the JWT and validate it
     *
     * @return stdClass
     */
    public function decodeJWT($encToken)
    {
        return Auth0JWT::decode($encToken, $this->client_id, $this->client_secret);
    }
}
