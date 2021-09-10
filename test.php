<?php
require __DIR__ . '/vendor/autoload.php';

try {
    define("ENVIRONMENT", (getenv("ENVIRONMENT") ? getenv("ENVIRONMENT") : "local"));
    new Lucinda\UnitTest\ConsoleController("unit-tests.xml", ENVIRONMENT);
} catch (Exception $e) {
    if ($e->getMessage()=="SQLSTATE[HY000] [1049] Unknown database 'test'") {
        echo "Please execute this SQL to set up mock database: https://www.lucinda-framework.com/tutorials/unit-testing#setting_database\n";
    } else if ($e->getMessage()=="SQLSTATE[HY000] [2002] Connection refused") {
        echo "Please start MySQL server or install it along with its PHP driver! If you're using a different SQL vendor, modify 'sql' tag accordingly in: tests/mocks/stdout.xml\n";
    } else if ($e->getMessage()=="Connection refused") {
        echo "Please start Redis server or install it along with its PHP driver! If you're using a different NoSQL vendor, modify 'nosql' tag accordingly in: tests/mocks/stdout.xml\n";
    } else {
        echo $e->getMessage()."\n";
    }
}
