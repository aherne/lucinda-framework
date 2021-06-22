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
$application = new Lucinda\STDOUT\Application("stdout.xml");
if (CACHE_TYPE == "sql") {
    // configures sql data source
    new Lucinda\SQL\Wrapper($application->getTag("sql")->xpath(".."), ENVIRONMENT);

    // sets cache
    $cache = new Lucinda\Project\DAO\SqlMigrationCache();

    // loads SQL helper
    require("helpers/SQL.php");
} else {
    // configures nosql data source
    new Lucinda\NoSQL\Wrapper($application->getTag("nosql")->xpath(".."), ENVIRONMENT);

    // sets cache
    $cache = new Lucinda\Project\DAO\NoSqlMigrationCache();
}

// run migrations based on console input
$executor = new Lucinda\Migration\ConsoleExecutor($folder, $cache);
$executor->execute((isset($argv[1]) ? $argv[1] : "migrate"), (isset($argv[2]) ? $argv[2] : ""));
