<?php

require __DIR__ . '/bootstrap.php';

use Lucinda\Framework\Persistence\SqlVendor;
use Test\Lucinda\Project\Persistence\SchemaChecks;

try {
    if (in_array('sqlite', PDO::getAvailableDrivers(), true)) {
        $checks = new SchemaChecks(new PDO('sqlite::memory:'), SqlVendor::SQLite);
        echo 'SQLite schema checks passed (' . $checks->run() . ").\n";
    } else {
        echo "SQLite schema checks skipped: pdo_sqlite unavailable.\n";
    }
    $database = getenv('PERSISTENCE_TEST_POSTGRESQL_DATABASE') ?: '';
    if ($database === '') {
        echo "PostgreSQL schema checks skipped: supply an isolated database.\n";
        exit(0);
    }
    if (!preg_match('/^framework_dao_test_[a-z0-9_]+$/D', $database)) {
        throw new RuntimeException('Schema tests require an isolated database');
    }
    $host = getenv('PERSISTENCE_TEST_POSTGRESQL_HOST') ?: '127.0.0.1';
    $port = getenv('PERSISTENCE_TEST_POSTGRESQL_PORT') ?: '5432';
    $pdo = new PDO(
        'pgsql:host=' . $host . ';port=' . $port . ';dbname=' . $database,
        getenv('PERSISTENCE_TEST_POSTGRESQL_USER') ?: 'postgres',
        getenv('PERSISTENCE_TEST_POSTGRESQL_PASSWORD') ?: ''
    );
    $checks = new SchemaChecks($pdo, SqlVendor::PostgreSQL);
    echo 'PostgreSQL schema checks passed (' . $checks->run() . ").\n";
} catch (Throwable $error) {
    fwrite(STDERR, get_class($error) . ': ' . $error->getMessage() . "\n");
    exit(1);
}
