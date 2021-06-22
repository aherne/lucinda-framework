<?php
require __DIR__ . '/vendor/autoload.php';

/**
 * DB for tests:
 *
 * mysql> create database test;
 * mysql> create user test@localhost identified by 'me';
 * mysql> grant all on test.* to test@localhost;
 */
try {
    define("ENVIRONMENT", (getenv("ENVIRONMENT") ? getenv("ENVIRONMENT") : "local"));
    new Lucinda\UnitTest\ConsoleController("unit-tests.xml", ENVIRONMENT);
} catch (Exception $e) {
    var_dump($e);
    echo $e->getMessage();
}
