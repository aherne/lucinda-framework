<?php
require __DIR__.'/vendor/autoload.php';

// Cache type is deliberately explicit; install the matching db-<vendor>.sql beforehand.
define('ENVIRONMENT', getenv('ENVIRONMENT') ?: 'local');
define('CACHE_TYPE', 'sql');
$folder = __DIR__.'/migrations';
if (!is_dir($folder) && !mkdir($folder, 0770, true) && !is_dir($folder)) throw new RuntimeException('Cannot create migrations folder');
if (CACHE_TYPE === 'sql') {
    $listener = new Lucinda\Project\EventListeners\SQLDataSource();
    $provider = $listener->run();
    $sql = new Lucinda\Framework\Persistence\SqlExecutor(
        $provider->getConnection()
    );
    $cache = new Lucinda\Project\DAO\SQLMigrationCache($sql);
} else {
    $listener = new Lucinda\Project\EventListeners\NoSQLDataSource();
    $provider = $listener->run();
    $nosql = new Lucinda\Framework\Persistence\NoSqlExecutor(
        $provider->getAtomicDriver()
    );
    $cache = new Lucinda\Project\DAO\NoSQLMigrationCache($nosql);
}
$executor = new Lucinda\Migration\ConsoleExecutor($folder, $cache);
$executor->execute($argv[1] ?? 'migrate', $argv[2] ?? '');
