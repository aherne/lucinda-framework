<?php
$_SERVER = array (
    'SERVER_SOFTWARE' => 'Apache/2.4.29 (Ubuntu)',
    'SERVER_NAME' => 'www.test.local',
    'SERVER_ADDR' => '127.0.0.1',
    'SERVER_PORT' => '80',
    'SERVER_ADMIN' => 'webmaster@localhost',
    'REMOTE_ADDR' => '127.0.0.1',
    'REMOTE_PORT' => '44074',
    'DOCUMENT_ROOT' => '/var/www/html/test',
    'SCRIPT_FILENAME' => '/var/www/html/test/index.php',
    'REQUEST_METHOD' => 'GET',
    'QUERY_STRING' => '',
    'REQUEST_URI' => '/index'
);

require_once(dirname(__DIR__)."/vendor/lucinda/mvc/src/AttributesFactory.php");
require_once(dirname(__DIR__)."/vendor/lucinda/mvc/src/Request.php");
require_once(dirname(__DIR__)."/vendor/lucinda/mvc/src/implemented/PageValidator.php");
