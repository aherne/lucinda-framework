<?php

require __DIR__."/vendor/autoload.php";

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
    require __DIR__."/helpers/SQL.php";

    // configures sql data source
    if ($ref = (string) $xml->sql["ref"]) {
        $xml = simplexml_load_file($ref.".xml");
    }
    new Lucinda\SQL\Wrapper($xml, ENVIRONMENT);

    // sets cache
    $cache = new Lucinda\Project\DAO\SQLMigrationCache();
} else {
    require __DIR__."/helpers/NoSQL.php";

    // configures nosql data source
    if ($ref = (string) $xml->nosql["ref"]) {
        $xml = simplexml_load_file($ref.".xml");
    }
    new Lucinda\NoSQL\Wrapper($xml, ENVIRONMENT);

    // sets cache
    $cache = new Lucinda\Project\DAO\NoSQLMigrationCache();
}

// run migrations based on console input
$executor = new Lucinda\Migration\ConsoleExecutor($folder, $cache);
$executor->execute(($argv[1] ?? "migrate"), ($argv[2] ?? ""));
