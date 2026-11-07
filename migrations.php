<?php
require __DIR__.'/vendor/autoload.php';

// Cache type is deliberately explicit; schema installation uses db.sql beforehand.
define('ENVIRONMENT', getenv('ENVIRONMENT') ?: 'local');
define('CACHE_TYPE', 'sql');
$folder = __DIR__.'/migrations';
if (!is_dir($folder) && !mkdir($folder, 0770, true) && !is_dir($folder)) throw new RuntimeException('Cannot create migrations folder');
if (CACHE_TYPE === 'sql') {
    $cache = new Lucinda\Project\DAO\SQLMigrationCache();
} else {
    $cache = new Lucinda\Project\DAO\NoSQLMigrationCache();
}
$executor = new Lucinda\Migration\ConsoleExecutor($folder, $cache);
$executor->execute($argv[1] ?? 'migrate', $argv[2] ?? '');
