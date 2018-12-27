<?php

if (!@include __DIR__ . '/../vendor/autoload.php') {
    die(<<<'EOT'
You must set up the project dependencies, run the following commands:
    curl -s http://getcomposer.org/installer | php
    php composer.phar install

EOT
    );
}

$loader = require dirname(__DIR__).'/vendor/autoload.php';
$loader->add('Auth0\\JWTAuthBundle\\', __DIR__);
