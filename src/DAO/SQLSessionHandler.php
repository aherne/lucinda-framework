<?php

namespace Lucinda\Project\DAO;

use Lucinda\Project\Persistence\Builders\SessionDataBuilder;
use Lucinda\Project\Persistence\Queries\Sessions\DeleteExpiredSessions;
use Lucinda\Project\Persistence\Queries\Sessions\DeleteSession;
use Lucinda\Project\Persistence\Queries\Sessions\ReadSession;
use Lucinda\Project\Persistence\Queries\Sessions\WriteSession;
use Lucinda\Project\Persistence\SqlExecutor;

/**
 * Persists PHP session payloads in SQL independently of a locking strategy
 *
 * Serialized bytes are upserted without changing session identifiers. Use
 * LockingSQLSessionHandler for concurrent session read-modify-write lifecycles.
 */
class SQLSessionHandler implements \SessionHandlerInterface
{
    /** Executor used by this session storage implementation */
    protected SqlExecutor $sql;

    /**
     * Captures an injected executor or a lazily configured SQL connection
     *
     * @param SqlExecutor|null $sql Executor sharing the caller's connection
     */
    public function __construct(?SqlExecutor $sql = null)
    {
        $this->sql = $sql ?? new SqlExecutor();
    }

    /**
     * Accepts PHP's session storage initialization request
     *
     * @param string $savePath PHP session storage path, unused for SQL
     * @param string $sessionName Configured PHP session cookie name
     * @return bool True because storage connects lazily on first operation
     */
    public function open(string $savePath, string $sessionName): bool
    {
        return true;
    }

    /**
     * Completes the storage-only session lifecycle
     *
     * @return bool True because no storage cursor needs explicit cleanup
     */
    public function close(): bool
    {
        return true;
    }

    /**
     * Reads an unexpired session payload from storage
     *
     * @param string $sessionID Session identifier supplied by PHP
     * @return string|false Stored bytes or an empty string for a new session
     * @throws \Throwable If selection or row validation fails
     */
    public function read(string $sessionID): string|false
    {
        $query = new ReadSession($sessionID, time());
        $selection = $this->sql->select($query);
        $row = $selection->getFirstRow();
        if ($row === null) {
            return '';
        }
        $builder = new SessionDataBuilder($row);
        $session = $builder->build();
        return $session->getPayload();
    }

    /**
     * Creates or replaces the session's serialized payload
     *
     * @param string $sessionID Session identifier supplied by PHP
     * @param string $sessionData Serialized session bytes
     * @return bool True after the payload has been persisted
     * @throws \Throwable If persistence fails
     */
    public function write(string $sessionID, string $sessionData): bool
    {
        $lifetime = max(1, (int) ini_get('session.gc_maxlifetime'));
        $query = new WriteSession(
            $sessionID,
            $sessionData,
            time() + $lifetime
        );
        $this->sql->write($query);
        return true;
    }

    /**
     * Deletes a session without requiring its payload to exist
     *
     * @param string $sessionID Session identifier supplied by PHP
     * @return bool True after the deletion operation completed
     * @throws \Throwable If deletion fails
     */
    public function destroy(string $sessionID): bool
    {
        $this->sql->write(new DeleteSession($sessionID));
        return true;
    }

    /**
     * Deletes payloads whose stored expiration time has elapsed
     *
     * @param int $maxlifetime PHP lifetime argument, superseded by stored
     *     expiry
     * @return int|false Number of expired records deleted
     * @throws \Throwable If maintenance deletion fails
     */
    public function gc(int $maxlifetime): int|false
    {
        $result = $this->sql->write(new DeleteExpiredSessions(time()));
        return $result->getAffectedRows();
    }
}
