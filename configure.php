<?php
require_once("vendor/lucinda/framework-configurer/src/CommandRunner.php");

ini_set("display_errors", 1);
error_reporting(E_ALL);
try {
    if (sizeof($argv)<2) {
        throw new Exception("Option parameter is missing!");
    }
    
    // windows fix: start
    if (!function_exists("readline")) {
        function readline($prompt = '')
        {
            echo $prompt;
            return trim(fgets(STDIN));
        }
    }
    // windows fix: end
    
    // runs user-selected option
    $commandRunner = new Lucinda\Configurer\CommandRunner();
    $commandRunner->run($argv[1], (sizeof($argv)>2?array_slice($argv, 2):array()));
} catch (Exception $e) {
    echo "FATAL ERROR: ".$e->getMessage()."\n";
}
