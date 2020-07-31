<?php
defined('ALTUMCODE') || die();

$config = [
    'database_host'        => getenv('MYSQL_HOST'),
    'database_username'    => getenv('MYSQL_USER'),
    'database_password'    => getenv('MYSQL_PASSWORD'),
    'database_name'        => getenv('MYSQL_DATABASE'),
    'database_port'        => getenv('MYSQL_PORT'),
    'url'                  => getenv('PRODUCT_URL')
];
