<?php

namespace Test\Lucinda\Project\Support;

use Lucinda\Framework\NoSQL\AtomicDriver;
use Lucinda\Framework\NoSQL\AtomicDriver\RedisAtomicDriver;
use Lucinda\Framework\NoSqlDriverProvider;
use Lucinda\Framework\Persistence\NoSqlExecutor;

/**
 * Owns real Redis storage under a private physical namespace for one test case
 *
 * Production DAO operations use the framework atomic adapter and executor.
 * Cleanup visits only this fixture's native key prefix. Other Redis databases
 * and keys are preserved, including when Date uses its default logical prefix.
 */
final class RedisFixture
{
    /** Provider owning the actual Redis connection used by this fixture */
    private NoSqlDriverProvider $provider;

    /** Atomic adapter with a unique physical namespace */
    private AtomicDriver $driver;

    /** Executor shared by the DAOs participating in this test case */
    private NoSqlExecutor $executor;

    /** Unique physical key prefix used only by this fixture */
    private string $prefix;

    /** Whether this fixture has already removed its keys */
    private bool $closed = false;

    /**
     * Connects to the installed Redis server and allocates a private namespace
     *
     * @throws \Throwable If the Redis server cannot be reached or configured
     */
    public function __construct()
    {
        $xml = $this->getConfiguration();
        $this->provider = new NoSqlDriverProvider($xml);
        $this->prefix = 'framework-test:' . bin2hex(random_bytes(12)) . ':';
        $this->driver = new RedisAtomicDriver(
            $this->provider->getDriver(),
            $this->prefix
        );
        $this->executor = new NoSqlExecutor($this->driver, 'records:');
    }

    /**
     * Gets explicit listener configuration for the real Redis endpoint
     *
     * @return \SimpleXMLElement XML document with the Redis server definition
     */
    public function getConfiguration(): \SimpleXMLElement
    {
        $xml = new \SimpleXMLElement('<xml><nosql><server/></nosql></xml>');
        $xml->nosql->server['driver'] = 'redis';
        $xml->nosql->server['host'] =
            (getenv('PERSISTENCE_TEST_REDIS_HOST') ?: '127.0.0.1') . ':'
            . (getenv('PERSISTENCE_TEST_REDIS_PORT') ?: '6379');
        $xml->nosql->server['timeout'] = '2';
        $xml->nosql->server['persistent'] = '0';
        return $xml;
    }

    /**
     * Gets the real atomic driver for Date's existing injection API
     *
     * @return AtomicDriver Adapter owning only this fixture's physical keys
     */
    public function getDriver(): AtomicDriver
    {
        return $this->driver;
    }

    /**
     * Gets the executor shared by this case's NoSQL DAOs
     *
     * @return NoSqlExecutor Executor connected to real isolated Redis records
     */
    public function getExecutor(): NoSqlExecutor
    {
        return $this->executor;
    }

    /**
     * Removes only native Redis keys created under this case's private prefix
     *
     * @return void
     */
    public function close(): void
    {
        if ($this->closed) {
            return;
        }
        $client = $this->provider->getDriver()->getDriver();
        $cursor = null;
        do {
            $keys = $client->scan($cursor, $this->prefix . '*', 200);
            if (is_array($keys)) {
                foreach ($keys as $key) {
                    if (str_starts_with($key, $this->prefix)) {
                        $client->del($key);
                    }
                }
            }
        } while ($cursor !== 0);
        $this->closed = true;
    }

    /** Removes only this fixture's namespaced records when it leaves scope. */
    public function __destruct()
    {
        $this->close();
    }
}
