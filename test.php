<?php
require __DIR__ . '/vendor/autoload.php';
try {
    define("ENVIRONMENT", "local");
    new Lucinda\UnitTest\ConsoleController("unit-tests.xml", "local");
} catch (Exception $e) {
    echo $e->getMessage();
}
