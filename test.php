<?php

require __DIR__ . '/vendor/autoload.php';

try {
    define("ENVIRONMENT", (getenv("ENVIRONMENT") ? getenv("ENVIRONMENT") : "local"));
    new Lucinda\UnitTest\ConsoleController("unit-tests.xml", ENVIRONMENT);
} catch (\Throwable $e) {
    if ($e->getMessage()=="SQLSTATE[HY000] [1049] Unknown database 'test'" || str_starts_with($e->getMessage(), "SQLSTATE[HY000] [1045] Access denied for user")) {
        echo "Please execute unit-tests.sql as mysql root\n";
    } elseif ($e->getMessage()=="SQLSTATE[HY000] [2002] Connection refused") {
        echo "Please start MySQL server or install it along with its PHP driver! If you're using a different SQL vendor, modify 'sql' tag accordingly in: tests/mocks/stdout.xml\n";
    } elseif ($e->getMessage()=="Connection refused") {
        echo "Please start Redis server or install it along with its PHP driver! If you're using a different NoSQL vendor, modify 'nosql' tag accordingly in: tests/mocks/stdout.xml\n";
    } else {
        echo $e->getMessage()."\n";
    }
}
