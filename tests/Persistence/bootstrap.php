<?php

require_once dirname(__DIR__, 2) . '/vendor/autoload.php';

use Lucinda\WebSecurity\DAO\OAuth2\UserInformation;
use Test\Lucinda\Project\Persistence\ProviderUser;
use Test\Lucinda\Project\Persistence\FixtureQuery;

function persistenceTestSql(): \Lucinda\Project\Persistence\SqlExecutor
{
    $database = getenv('PERSISTENCE_TEST_DATABASE') ?: '';
    if (!preg_match('/^framework_dao_test_[a-z0-9_]+$/D', $database)) {
        throw new RuntimeException('Integration database must use the '
            . 'isolated framework_dao_test_ prefix');
    }
    $xml = new SimpleXMLElement('<server/>');
    foreach (
        [
        'driver' => 'mysql',
        'host' => getenv('PERSISTENCE_TEST_HOST') ?: 'localhost',
        'username' => getenv('PERSISTENCE_TEST_USER') ?: 'root',
        'password' => getenv('PERSISTENCE_TEST_PASSWORD') ?: '',
        'schema' => $database,
        'charset' => 'utf8mb4',
        'autocommit' => '1',
        ] as $key => $value
    ) {
        $xml->addAttribute($key, $value);
    }
    $connection = new Lucinda\SQL\Connection();
    $connection->connect(new Lucinda\SQL\DataSource($xml));
    return new Lucinda\Project\Persistence\SqlExecutor($connection);
}
function persistenceTestInsert(
    Lucinda\Project\Persistence\SqlExecutor $sql,
    string $table,
    array $data
): int {
    $query = new Lucinda\Query\Insert($table);
    $query->columns(array_keys($data));
    $parameters = []; foreach ($data as $column => $value) {
        $parameters[':' . $column] = $value;
    }
    $query->values(array_keys($parameters));
    return $sql->write(new FixtureQuery(
        $query,
        $parameters
    ))->getInsertId();
}
/**
 * Creates a provider identity with a shared email and distinct remote ID
 *
 * @param string $remote Provider-assigned identity identifier
 * @return UserInformation Fixture used by OAuth2 integration checks
 */
function persistenceTestUser(string $remote): UserInformation
{
    return new ProviderUser($remote);
}

/**
 * Opens an explicit NoSQL adapter in a unique integration-test namespace
 *
 * @param string $backend Backend selected by the test runner
 * @param string $prefix Unique namespace used only by these checks
 * @return \Lucinda\Project\Persistence\NoSqlExecutor Isolated executor
 * @throws RuntimeException If the namespace or backend is invalid
 */
function persistenceTestNoSql(
    string $backend,
    string $prefix
): \Lucinda\Project\Persistence\NoSqlExecutor {
    if (!str_starts_with($prefix, 'framework-test:')) {
        throw new RuntimeException('NoSQL tests require an isolated namespace');
    }
    $adapter = persistenceTestAtomicDriver($backend);
    return new \Lucinda\Project\Persistence\NoSqlExecutor($adapter, $prefix);
}

/**
 * Opens an explicit framework atomic adapter for a selected test endpoint
 *
 * @param string $backend Backend name selected by the integration runner
 * @return \Lucinda\Framework\NoSQL\AtomicDriver Adapter owning its driver
 * @throws RuntimeException If the backend name is unsupported
 */
function persistenceTestAtomicDriver(
    string $backend
): \Lucinda\Framework\NoSQL\AtomicDriver {
    $xml = new SimpleXMLElement('<server/>');
    switch ($backend) {
        case 'redis':
        case 'valkey':
            $port = $backend === 'valkey'
                ? getenv('PERSISTENCE_TEST_VALKEY_PORT')
                : getenv('PERSISTENCE_TEST_REDIS_PORT');
            $xml->addAttribute('host', '127.0.0.1:' . $port);
            if ($backend === 'valkey') {
                $driver = new \Lucinda\NoSQL\Vendor\Valkey\Driver();
                $data = new \Lucinda\NoSQL\Vendor\Valkey\DataSource($xml);
            } else {
                $driver = new \Lucinda\NoSQL\Vendor\Redis\Driver();
                $data = new \Lucinda\NoSQL\Vendor\Redis\DataSource($xml);
            }
            $driver->connect($data);
            $adapter = new \Lucinda\Framework\NoSQL\RedisAtomicDriver($driver);
            break;
        case 'memcached':
            $xml->addAttribute('host', '127.0.0.1:'
                . getenv('PERSISTENCE_TEST_MEMCACHED_PORT'));
            $driver = new \Lucinda\NoSQL\Vendor\Memcached\Driver();
            $driver->connect(
                new \Lucinda\NoSQL\Vendor\Memcached\DataSource($xml)
            );
            $adapter = new \Lucinda\Framework\NoSQL\MemcachedAtomicDriver(
                $driver
            );
            break;
        case 'apcu':
            $adapter = new \Lucinda\Framework\NoSQL\APCuAtomicDriver(
                new \Lucinda\NoSQL\Vendor\APCu\Driver()
            );
            break;
        default:
            throw new RuntimeException('Unknown NoSQL test backend');
    }
    return $adapter;
}
