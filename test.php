<?php
require __DIR__ . '/vendor/autoload.php';

try {
    define("ENVIRONMENT", (getenv("ENVIRONMENT") ? getenv("ENVIRONMENT") : "local"));
    new Lucinda\UnitTest\ConsoleController("unit-tests.xml", ENVIRONMENT);
} catch (Exception $e) {
    if ($e->getMessage()=="SQLSTATE[HY000] [1049] Unknown database 'test'") {
        echo "Please execute this SQL to set up mock database: https://www.lucinda-framework.com/tutorials/unit-testing#setting_database\n";
    } else {
        echo $e->getMessage()."\n";
    }    
}
