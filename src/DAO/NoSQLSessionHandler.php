<?php

namespace Lucinda\Project\DAO;

use Lucinda\Project\Persistence\Redis\SessionStore;
use Lucinda\Project\Persistence\RedisExecutor;

/**
 * Adapts an owned Redis session store to PHP's session handler lifecycle
 *
 * The store owns lease acquisition, payload operations and release. Redis TTL
 * provides expiration, and expired leases cannot authorize stale writes.
 */
final class NoSQLSessionHandler implements \SessionHandlerInterface
{
    /** Session state and lease owner retained through the PHP lifecycle */
    private SessionStore $store;

    /**
     * Captures an executor and creates the session lease owner
     *
     * @param RedisExecutor|null $executor Injected Redis connection owner
     */
    public function __construct(?RedisExecutor $executor = null)
    {
        if ($executor === null) {
            $executor = new RedisExecutor();
        }
        $this->store = new SessionStore($executor);
    }

    /**
     * Accepts PHP's session storage initialization request
     *
     * @param string $savePath PHP session storage path, unused for Redis
     * @param string $sessionName Configured PHP session cookie name
     * @return bool True because the store connects lazily on first operation
     */
    public function open(string $savePath, string $sessionName): bool
    {
        return true;
    }

    /**
     * Releases the lease retained by this PHP session lifecycle
     *
     * @return bool True after lease release has been attempted successfully
     * @throws \Throwable If Redis cannot perform the release operation
     */
    public function close(): bool
    {
        $this->store->close();
        return true;
    }

    /**
     * Reads session bytes and retains ownership until close
     *
     * @param string $sessionID Session identifier supplied by PHP
     * @return string|false Stored bytes or an empty string for a new session
     * @throws \Throwable If storage access or lease acquisition fails
     */
    public function read(string $sessionID): string|false
    {
        return $this->store->read($sessionID);
    }

    /**
     * Persists serialized session bytes while lease ownership remains valid
     *
     * @param string $sessionID Session identifier supplied by PHP
     * @param string $sessionData Serialized session bytes
     * @return bool True after the payload was persisted
     * @throws \Throwable If persistence fails or the lease expires
     */
    public function write(string $sessionID, string $sessionData): bool
    {
        $lifetime = max(1, (int) ini_get('session.gc_maxlifetime'));
        $this->store->write($sessionID, $sessionData, $lifetime);
        return true;
    }

    /**
     * Deletes a session payload without requiring an existing record
     *
     * @param string $sessionID Session identifier supplied by PHP
     * @return bool True after the deletion operation completed
     * @throws \Throwable If deletion fails or the lease expires
     */
    public function destroy(string $sessionID): bool
    {
        $this->store->destroy($sessionID);
        return true;
    }

    /**
     * Reports that Redis TTL handles expiration without an explicit scan
     *
     * @param int $maxlifetime PHP lifetime argument, unused with Redis TTL
     * @return int|false Zero records explicitly deleted by this handler
     */
    public function gc(int $maxlifetime): int|false
    {
        return 0;
    }
}
