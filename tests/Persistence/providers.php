<?php

require __DIR__ . '/bootstrap.php';

use Lucinda\Project\DAO\NoSQLLoginThrottler;
use Lucinda\Project\EventListeners\NoSQLDataSource;
use Lucinda\Project\EventListeners\SQLDataSource;
use Lucinda\Framework\Persistence\NoSqlExecutor;
use Lucinda\Framework\Persistence\Queries\ConnectionSettings\ReadAutocommit;
use Lucinda\Framework\Persistence\SqlExecutor;
use Test\Lucinda\Project\Persistence\FixtureQuery;
use Lucinda\SQL\DataType;


try {
    $database = getenv('PERSISTENCE_TEST_DATABASE') ?: '';
    if (!preg_match('/^framework_dao_test_[a-z0-9_]+$/D', $database)) {
        throw new RuntimeException('Provider tests require an isolated DB');
    }
    $requiresDriver = false;
    try {
        new NoSqlExecutor();
    } catch (ArgumentCountError) {
        $requiresDriver = true;
    }
    if (!$requiresDriver) {
        throw new RuntimeException('NoSQL must require an injected driver');
    }
    $count = 1;
    $attributes = [
        'driver' => 'mysql',
        'port' => getenv('PERSISTENCE_TEST_PORT') ?: '3306',
        'host' => getenv('PERSISTENCE_TEST_HOST') ?: 'localhost',
        'username' => getenv('PERSISTENCE_TEST_USER') ?: 'root',
        'password' => getenv('PERSISTENCE_TEST_PASSWORD') ?: '',
        'schema' => $database,
        'charset' => 'utf8mb4',
        'autocommit' => '1',
    ];
    $xml = new SimpleXMLElement('<xml><sql><server/></sql></xml>');
    foreach ($attributes as $name => $value) {
        $xml->sql->server->addAttribute($name, $value);
    }
    $sqlProvider = (new SQLDataSource($xml))->run();
    if (!$sqlProvider instanceof Lucinda\Framework\SqlConnectionProvider) {
        throw new RuntimeException('SQL listener must publish its provider');
    }
    $count++;
    $query = new Lucinda\Query\Select('DUAL');
    $fields = $query->fields();
    $fields->add('CONNECTION_ID()', 'value');
    $operation = new FixtureQuery($query, []);
    $first = new SqlExecutor($sqlProvider->getConnection());
    $second = new SqlExecutor($sqlProvider->getConnection());
    $firstID = $first->read($operation)->toValue(DataType::INTEGER);
    $secondID = $second->read($operation)->toValue(DataType::INTEGER);
    if (!($firstID === $secondID)) {
        throw new RuntimeException('Listener connection must be shared');
    }
    $injected = persistenceTestSql();
    $injectedID = $injected->read($operation)->toRow();
    if ($injectedID->getInteger('value') === $firstID) {
        throw new RuntimeException('Explicit SQL connection was ignored');
    }
    $named = new SimpleXMLElement('<xml><sql/></xml>');
    foreach (['primary', 'secondary'] as $serverName) {
        $server = $named->sql->addChild('server');
        $server->addAttribute('name', $serverName);
        foreach ($attributes as $name => $value) {
            $server->addAttribute($name, $value);
        }
    }
    $namedSqlProvider = (new SQLDataSource($named))->run();
    $primary = new SqlExecutor($namedSqlProvider->getConnection('primary'));
    $secondary = new SqlExecutor(
        $namedSqlProvider->getConnection('secondary')
    );
    $primaryID = $primary->read($operation)->toValue(DataType::INTEGER);
    $secondaryID = $secondary->read($operation)->toRow();
    if ($primaryID === $secondaryID->getInteger('value')) {
        throw new RuntimeException('Named SQL servers must be independent');
    }
    $retainedID = $first->read($operation)->toValue(DataType::INTEGER);
    if ($retainedID !== $firstID) {
        throw new RuntimeException('Executor lost its original provider');
    }
    $count += 4;
    $backends = [];
    if (getenv('PERSISTENCE_TEST_REDIS_PORT')) {
        $backends['redis'] = '127.0.0.1:'
            . getenv('PERSISTENCE_TEST_REDIS_PORT');
    }
    if (getenv('PERSISTENCE_TEST_VALKEY_PORT')) {
        $backends['valkey'] = '127.0.0.1:'
            . getenv('PERSISTENCE_TEST_VALKEY_PORT');
    }
    if (getenv('PERSISTENCE_TEST_MEMCACHED_PORT')) {
        $backends['memcached'] = '127.0.0.1:'
            . getenv('PERSISTENCE_TEST_MEMCACHED_PORT');
    }
    if (getenv('PERSISTENCE_TEST_APCU')) {
        $backends['apcu'] = '';
    }
    foreach ($backends as $backend => $host) {
        $xml = new SimpleXMLElement('<xml><nosql><server/></nosql></xml>');
        $xml->nosql->server->addAttribute('driver', $backend);
        if ($host !== '') {
            $xml->nosql->server->addAttribute('host', $host);
        }
        $provider = (new NoSQLDataSource($xml))->run();
        if (!$provider instanceof Lucinda\Framework\NoSqlDriverProvider) {
            throw new RuntimeException('NoSQL listener must publish a provider');
        }
        $count++;
        $prefix = 'framework-test:' . bin2hex(random_bytes(12)) . ':';
        $original = $provider->getDriver();
        $atomic = $provider->getAtomicDriver();
        if ($provider->getAtomicDriver() !== $atomic) {
            throw new RuntimeException('Atomic adapter must be cached');
        }
        $count++;
        if ($provider->getDriver() !== $original) {
            throw new RuntimeException('Original driver mapping changed');
        }
        $count++;
        $ordinaryKey = $prefix . 'ordinary';
        $original->set($ordinaryKey, 'library', 1);
        $atomic->set($ordinaryKey, 'atomic', 1);
        if (
            $original->get($ordinaryKey) !== 'library'
            || $atomic->get($ordinaryKey) !== 'atomic'
        ) {
            throw new RuntimeException('Atomic namespace overlaps raw keys');
        }
        $original->delete($ordinaryKey);
        $atomic->delete($ordinaryKey);
        $count++;
        $executor = new NoSqlExecutor($provider->getAtomicDriver(), $prefix);
        $other = new NoSqlExecutor($provider->getAtomicDriver(), $prefix);
        $left = new NoSQLLoginThrottler($executor, 2, 5);
        $right = new NoSQLLoginThrottler($other, 2, 5);
        $left->penalize('provider', '127.0.0.1');
        $right->penalize('provider', '127.0.0.1');
        if (!$left->isThrottled('provider', '127.0.0.1')) {
            throw new RuntimeException($backend . ' provider did not share data');
        }
        $count++;
        $bucket = new Lucinda\Framework\Persistence\ThrottleBucket(
            'form',
            'provider',
            '127.0.0.1'
        );
        $atomic->delete($executor->getKeyspace()->getKey(
            'throttle',
            $bucket->getKey()
        ));
    }
    if (count($backends) > 1) {
        $xml = new SimpleXMLElement('<xml><nosql/></xml>');
        foreach ($backends as $backend => $host) {
            $server = $xml->nosql->addChild('server');
            $server->addAttribute('name', $backend);
            $server->addAttribute('driver', $backend);
            if ($host !== '') {
                $server->addAttribute('host', $host);
            }
        }
        $namedNoSqlProvider = (new NoSQLDataSource($xml))->run();
        $prefix = 'framework-test:' . bin2hex(random_bytes(12)) . ':';
        foreach (array_keys($backends) as $backend) {
            $named = new NoSqlExecutor(
                $namedNoSqlProvider->getAtomicDriver($backend),
                $prefix
            );
            $key = $named->getKeyspace()->getKey('test', 'named');
            $snapshot = $named->read($key);
            $named->replace($snapshot, ['backend' => $backend], 1);
            if ($named->read($key)->getValue()['backend'] !== $backend) {
                throw new RuntimeException('Named NoSQL server was ignored');
            }
            $count++;
        }
        $missingDefault = false;
        try {
            $namedNoSqlProvider->getAtomicDriver();
        } catch (Lucinda\NoSQL\ConnectionException) {
            $missingDefault = true;
        }
        if (!$missingDefault) {
            throw new RuntimeException('Named servers need an explicit name');
        }
        $count++;
    }
    echo "Listener/provider checks passed ($count).\n";
} catch (Throwable $error) {
    fwrite(STDERR, get_class($error) . ': ' . $error->getMessage() . "\n");
    exit(1);
}
