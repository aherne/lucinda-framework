<?php
require __DIR__ . '/vendor/autoload.php';
ini_set("display_errors",1);
/**
 * mysql> create database test;
 * mysql> create user test@localhost identified by 'me';
 * mysql> grant all on test.* to test@localhost;
 */
try {
    define("ENVIRONMENT", "local");
    new Lucinda\UnitTest\ConsoleController("unit-tests.xml", "local");
} catch (Exception $e) {
    echo $e->getMessage();
}
