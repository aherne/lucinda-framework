<?php
require __DIR__ . '/vendor/autoload.php';
ini_set("display_errors", 1);
error_reporting(E_ALL);
try {
    if (sizeof($argv)<2) {
        throw new Exception("Option parameter is missing!");
    }
    
    // runs user-selected option
    $commandRunner = new Lucinda\Configurer\CommandRunner();
    $commandRunner->run($argv[1], (sizeof($argv)>2?array_slice($argv, 2):array()));
} catch (Exception $e) {
    echo "FATAL ERROR: ".$e->getMessage()."\n";
}
