<?php
require __DIR__ . '/vendor/autoload.php';

/**
 * Before running tests you need to:
 * @see https://www.lucinda-framework.com/tutorials/unit-testing#setting_database
 */
try {
    define("ENVIRONMENT", (getenv("ENVIRONMENT") ? getenv("ENVIRONMENT") : "local"));
    new Lucinda\UnitTest\ConsoleController("unit-tests.xml", ENVIRONMENT);
} catch (Exception $e) {
    var_dump($e);
}
