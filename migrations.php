<?php
require(__DIR__."/vendor/autoload.php");

// defines development environment and cache type
define("ENVIRONMENT", (getenv("ENVIRONMENT") ? getenv("ENVIRONMENT") : "local"));
define("CACHE_TYPE", "sql");

// defines folder to store migrations and creates it if not exists
$folder = "migrations";
if (!file_exists($folder)) {
    mkdir($folder);
}

// configures cache
$cache = null;
$xml = simplexml_load_file("stdout.xml");
if (CACHE_TYPE == "sql") {
    // configures sql data source
    if ($ref = (string) $xml->sql["ref"]) {
        $xml = simplexml_load_file($ref.".xml");
    }
    new Lucinda\SQL\Wrapper($xml, ENVIRONMENT);

    // sets cache
    $cache = new Lucinda\Project\DAO\SqlMigrationCache();

    // loads SQL helper
    require("helpers/SQL.php");
} else {
    // configures nosql data source
    if ($ref = (string) $xml->nosql["ref"]) {
        $xml = simplexml_load_file($ref.".xml");
    }
    new Lucinda\NoSQL\Wrapper($xml, ENVIRONMENT);

    // sets cache
    $cache = new Lucinda\Project\DAO\NoSqlMigrationCache();
}

// run migrations based on console input
$executor = new Lucinda\Migration\ConsoleExecutor($folder, $cache);
$executor->execute((isset($argv[1]) ? $argv[1] : "migrate"), (isset($argv[2]) ? $argv[2] : ""));
