<?php

namespace Lucinda\Project\Persistence;

use Lucinda\Framework\NoSqlDriverProvider;
use Lucinda\Framework\XmlDetector;
use Lucinda\NoSQL\Vendor\Redis\Driver;
use Lucinda\Project\Persistence\Redis\Keyspace;
use Lucinda\Project\Persistence\Redis\Script;
use Lucinda\Project\Persistence\Redis\ScriptResult;

/**
 * Executes atomic scripts on one instance-owned Redis connection
 *
 * Storage policy belongs to the individual stores and script objects. This
 * executor knows only connection configuration, keyspace and script execution.
 *
 * @internal
 */
final class RedisExecutor
{
    /** Redis client retained for this executor's lifetime */
    private ?\Redis $client;

    /** Provider retained while a configured driver owns the connection */
    private ?NoSqlDriverProvider $provider = null;

    /** Key namespace shared by stores using this executor */
    private Keyspace $keyspace;

    /** Configured server name, empty for the single default server */
    private string $server;

    /**
     * Captures an injected client or lazily configured Redis server
     *
     * @param \Redis|null $client Connection injected by application setup
     * @param string|null $prefix Explicit key namespace or environment setting
     * @param string $server Configured NoSQL server name
     */
    public function __construct(
        ?\Redis $client = null,
        ?string $prefix = null,
        string $server = ''
    ) {
        $this->client = $client;
        $this->server = $server;
        if ($prefix === null) {
            $prefix = getenv('PERSISTENCE_NAMESPACE');
            if ($prefix === false || $prefix === '') {
                $prefix = 'framework:';
            }
        }
        $this->keyspace = new Keyspace($prefix);
    }

    /**
     * Resolves this executor's client on first use
     *
     * @return \Redis Connection retained by this executor
     * @throws \LogicException If the configured backend is not Redis-compatible
     * @throws \Throwable If configuration or connection setup fails
     */
    private function getClient(): \Redis
    {
        if ($this->client === null) {
            $path = dirname(__DIR__, 2) . '/xml/stdout/nosql';
            $detector = new XmlDetector($path);
            $this->provider = new NoSqlDriverProvider($detector->getXML());
            $driver = $this->provider->getDriver($this->server);
            if (!$driver instanceof Driver) {
                throw new \LogicException('Atomic storage requires Redis');
            }
            $this->client = $driver->getDriver();
        }
        return $this->client;
    }

    /**
     * Gets the namespace used by stores sharing this connection
     *
     * @return Keyspace Application-specific Redis key namespace
     */
    public function getKeyspace(): Keyspace
    {
        return $this->keyspace;
    }

    /**
     * Executes one script with independent key and argument bindings
     *
     * @param Script $script Atomic storage operation to execute
     * @return ScriptResult Validatable response from this operation
     * @throws \RuntimeException If Redis reports script execution failure
     * @throws \Throwable If client access fails
     */
    public function execute(Script $script): ScriptResult
    {
        $value = $this->getClient()->eval(
            $script->getSource(),
            $script->getParameters(),
            $script->getKeyCount()
        );
        if ($value === false) {
            throw new \RuntimeException('Redis script execution failed');
        }
        return new ScriptResult($value);
    }
}
