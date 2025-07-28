<?php
// config/bootstrap.php

use Symfony\Component\Dotenv\Dotenv;

require dirname(__DIR__).'/vendor/autoload.php';

// Charge .env puis .env.test si APP_ENV=test
if (class_exists(Dotenv::class)) {
    (new Dotenv())->bootEnv(dirname(__DIR__).'/.env');
}
