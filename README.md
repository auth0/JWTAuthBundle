# jwt-auth-bundle

JWT Authentication bundle for Symfony

## Important

v2.x.x provides compatibility with Symfony 3

for Symfony 2.x, stick to v1 branch

##Demo

Check the usage demo: https://github.com/auth0/jwt-auth-bundle/tree/master/example

## Installation

Check our docs page to get a complete guide on how to install it in an existing project or download a pre configured seedproject: https://auth0.com/docs/quickstart/backend/php-symfony/

If you are looking for a webapp integration, check this doc: https://auth0.com/docs/quickstart/webapp/symfony/

> If you find something wrong in our docs, PR are welcome in our docs repo: https://github.com/auth0/docs


##News

###Version 1.2 release

- PSR-4 compliance
- Support for API v2 & auth0-php 1.0

#### BC breaks

- Config
    - package name space changed to `jwt_auth`
    - added optional `secret_base64_encoded` field
- Removed dependency with Auth0. Now you can use non base64 tokens.
- The profile data is not longer a `stdClass`, is an asociative array.
- There are some BC issues related to the auth0-php changes. We recomment to read the [auth0-php README](https://github.com/auth0/Auth0-PHP).

#### Auth0 integration

This package has built in Auth0 integration (as you can check on the example) but is not mandatory. You can use this package to authenticate other JWT.

The auth-php SDK is used to decode the JWT and if you are woking with Auth0 you can inject to your `UserProvider` to get the user profile (as you can check on the [example](https://github.com/auth0/jwt-auth-bundle/blob/master/example/src/AppBundle/Security/A0UserProvider.php)).

## Issue Reporting

If you have found a bug or if you have a feature request, please report them at this repository issues section. Please do not report security vulnerabilities on the public GitHub issue tracker. The [Responsible Disclosure Program](https://auth0.com/whitehat) details the procedure for disclosing security issues.
