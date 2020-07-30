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
define('PROJECT_DIR', __DIR__);

/* Including packages from the composer dependencies */
require_once __DIR__ . '/vendor/autoload.php';

/* Including packages from the old vendor */
require_once __DIR__ . '/vendor-static/autoload.php';

/* Loading environment variables */
\Dotenv\Dotenv::create(__DIR__, '.env')->load();

require 'core/base.php';
