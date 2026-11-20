<?php

require __DIR__ . '/bootstrap.php';
require_once dirname(__DIR__, 2)
    . '/vendor/lucinda/framework-engine/test-support/Support/'
    . 'AtomicDriverContract.php';

use Test\Lucinda\Framework\NoSQL\AtomicDriverContract;
use Lucinda\Project\DAO;
use Lucinda\Framework\Persistence\ThrottleBucket;
use Test\Lucinda\Project\Persistence\NoSqlChecks;
use Test\Lucinda\Project\Persistence\NoSqlConcurrency;
use Test\Lucinda\Project\Persistence\NoSqlLockChecks;


$backends = [];
if (getenv('PERSISTENCE_TEST_REDIS_PORT')) {
    $backends[] = 'redis';
}
if (getenv('PERSISTENCE_TEST_VALKEY_PORT')) {
    $backends[] = 'valkey';
}
if (getenv('PERSISTENCE_TEST_MEMCACHED_PORT')) {
    $backends[] = 'memcached';
}
if (getenv('PERSISTENCE_TEST_APCU')) {
    $backends[] = 'apcu';
}
if ($backends === []) {
    echo "NoSQL integration checks skipped: enable test backend settings.\n";
    exit(0);
}
if (!function_exists('pcntl_fork')) {
    fwrite(STDERR, "NoSQL concurrency checks require ext-pcntl.\n");
    exit(1);
}
try {
    foreach ($backends as $backend) {
        $prefix = 'framework-test:' . bin2hex(random_bytes(12)) . ':';
        $executor = persistenceTestNoSql($backend, $prefix);
        $checks = new NoSqlChecks($executor);
        $lockChecks = new NoSqlLockChecks($executor);
        $workers = new NoSqlConcurrency($backend, $prefix);
        $contract = new AtomicDriverContract(
            persistenceTestAtomicDriver($backend),
            persistenceTestAtomicDriver($backend),
            $prefix
        );
        $keys = [];
        try {
            $contractCount = $contract->run();
            $count = $checks->run();
            $count += $lockChecks->run();
            $counterKey = $prefix . 'contract:parallel-counter';
            $keys[] = $counterKey;
            $counterDriver = persistenceTestAtomicDriver($backend);
            $counterDriver->set($counterKey, 0);
            $workers->run('atomic-counter', $counterKey, 16);
            $counterDriver = persistenceTestAtomicDriver($backend);
            if ($counterDriver->get($counterKey) !== 16) {
                throw new RuntimeException('Atomic driver increments lost');
            }
            $contractCount++;
            $bucket = new ThrottleBucket('form', 'parallel', '127.0.0.1');
            $key = $executor->getKeyspace()->getKey(
                'throttle',
                $bucket->getKey()
            );
            $keys[] = $key;
            $workers->run('throttle', 'parallel', 16);
            // Forked native-client destructors can close inherited sockets.
            $executor = persistenceTestNoSql($backend, $prefix);
            if ($executor->read($key)->getValue()['attempts'] !== 16) {
                throw new RuntimeException('Concurrent failure updates lost');
            }
            $count++;
            $keys[] = $executor->getKeyspace()->getKey('session', 'parallel');
            $workers->run('session', 'parallel', 8);
            $executor = persistenceTestNoSql($backend, $prefix);
            $session = new DAO\NoSQLSessionHandler($executor);
            if ($session->read('parallel') !== '8') {
                throw new RuntimeException('Concurrent session updates lost');
            }
            $session->close();
            $count++;
            $workers->run('migration', 'records', 8);
            $executor = persistenceTestNoSql($backend, $prefix);
            $cache = new DAO\NoSQLMigrationCache($executor);
            if (count($cache->read()) !== 8) {
                throw new RuntimeException('Concurrent migration updates lost');
            }
            $count++;
            $key = $executor->getKeyspace()->getKey('lock', 'parallel');
            $keys[] = $key;
            $workers->run('lock', 'parallel', 8);
            $executor = persistenceTestNoSql($backend, $prefix);
            if ($executor->read($key)->getValue()['value'] !== 8) {
                throw new RuntimeException('Concurrent locked updates lost');
            }
            $count++;
            if ($backend === 'redis') {
                $key = $executor->getKeyspace()->getKey('test', 'wrong-type');
                $keys[] = $key;
                $client = new Redis();
                $client->connect(
                    '127.0.0.1',
                    (int) getenv('PERSISTENCE_TEST_REDIS_PORT')
                );
                $client->hSet('lucinda:atomic:' . $key, 'field', 'value');
                $rejected = false;
                try {
                    $executor->read($key);
                } catch (Throwable $error) {
                    $rejected = str_contains($error->getMessage(), 'WRONGTYPE');
                }
                if (!$rejected) {
                    throw new RuntimeException('Redis errors became absence');
                }
                $count++;
            }
            echo "$backend atomic-driver checks passed ($contractCount).\n";
            echo "$backend persistence checks passed ($count).\n";
        } finally {
            foreach ($contract->getKeys() as $key) {
                $keys[] = $key;
            }
            foreach ($checks->getKeys() as $key) {
                $keys[] = $key;
            }
            foreach ($lockChecks->getKeys() as $key) {
                $keys[] = $key;
            }
            if ($backend === 'redis' || $backend === 'valkey') {
                $client = new Redis();
                $client->connect(
                    '127.0.0.1',
                    (int) getenv($backend === 'valkey'
                        ? 'PERSISTENCE_TEST_VALKEY_PORT'
                        : 'PERSISTENCE_TEST_REDIS_PORT')
                );
                foreach ($keys as $key) {
                    $client->del('lucinda:atomic:' . $key);
                }
            } elseif ($backend === 'memcached') {
                $client = new Memcached();
                $client->addServer(
                    '127.0.0.1',
                    (int) getenv('PERSISTENCE_TEST_MEMCACHED_PORT')
                );
                foreach ($keys as $key) {
                    $client->delete('lucinda:atomic:' . $key);
                }
            } else {
                $pattern = '/^' . preg_quote('lucinda:atomic:' . $prefix, '/') . '/';
                foreach (new APCUIterator($pattern) as $entry) {
                    apcu_delete($entry['key']);
                }
            }
        }
    }
} catch (Throwable $error) {
    fwrite(STDERR, get_class($error) . ': ' . $error->getMessage() . "\n");
    exit(1);
}
