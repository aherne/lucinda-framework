<?php

namespace Lucinda\Project\DAO;

use Lucinda\Framework\NoSQL\SessionStore;
use Lucinda\Framework\Persistence\NoSqlExecutor;

/**
 * Adapts an owned NoSQL session store to PHP's session handler lifecycle
 *
 * The store owns lease acquisition, payload operations and release. Logical
 * deadlines enforce expiration, and expired leases cannot permit stale writes.
 */
final class NoSQLSessionHandler extends NoSqlBasedDAO implements
    \SessionHandlerInterface
{
    /** Session state and lease owner retained through the PHP lifecycle */
    private SessionStore $store;

    /**
     * Captures an executor and creates the session lease owner
     *
     * @param NoSqlExecutor $executor Injected NoSQL connection owner
     */
    public function __construct(NoSqlExecutor $executor)
    {
        parent::__construct($executor);
        $this->store = new SessionStore($executor);
    }

    /**
     * Accepts PHP's session storage initialization request
     *
     * @param string $savePath PHP session storage path, unused for NoSQL
     * @param string $sessionName Configured PHP session cookie name
     * @return bool True because storage was supplied during construction
     */
    public function open(string $savePath, string $sessionName): bool
    {
        return true;
    }

    /**
     * Releases the lease retained by this PHP session lifecycle
     *
     * @return bool True after lease release has been attempted successfully
     * @throws \Throwable If NoSQL cannot perform the release operation
     */
    public function close(): bool
    {
        $this->store->close();
        return true;
    }

    /**
     * Extends the active session lease without extending payload retention
     *
     * Call before the current lease expires when a request needs more time.
     * Expired ownership cannot be recovered through renewal.
     *
     * @throws \Throwable If ownership was lost or storage access fails
     */
    public function renew(): void
    {
        $this->store->renew();
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
     * Reports that expired records are rejected without an explicit scan
     *
     * @param int $maxlifetime PHP lifetime argument, superseded by the record's
     *     stored expiration
     * @return int|false Zero records explicitly deleted by this handler
     */
    public function gc(int $maxlifetime): int|false
    {
        return 0;
    }
}
