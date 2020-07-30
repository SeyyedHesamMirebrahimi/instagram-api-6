<?php
/*
 * AltumCode
 *
 *
 * @web         https://altumcode.io/
 * @twitter     https://twitter.com/altumcode
 *
 */

/* Security purpose define */
define('ALTUMCODE', true);

/* Enabling debug mode is only for debugging / development purposes. */
define('DEBUG', true);

define('MYSQL_DEBUG', false);

/**
 * This makes our life easier when dealing with paths. Everything is relative
 * to the application root now.
 */
define('PROJECT_DIR', dirname(__DIR__));

/* Including packages from the dashboard project */
require_once __DIR__ . '/../vendor/autoload.php';

/* Loading environment variables */
\Dotenv\Dotenv::create(dirname(__DIR__), '.env')->load();

require 'core/base.php';
