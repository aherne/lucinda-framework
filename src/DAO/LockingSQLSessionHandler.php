<?php

namespace Lucinda\Project\DAO;

use Lucinda\Project\Persistence\Locking\Lock;
use Lucinda\Project\Persistence\Locking\SqlLock;
use Lucinda\Project\Persistence\SqlExecutor;

/**
 * Extends SQL session storage with exclusive ownership through session close
 *
 * The injected lock defines ownership behavior independently of payload SQL.
 * The default MySQL lock shares the storage executor and its connection.
 */
final class LockingSQLSessionHandler extends SQLSessionHandler implements Lock
{
    /** Exclusive owner retained through the session lifecycle */
    private Lock $lock;

    /**
     * Captures session storage and its independent locking strategy
     *
     * @param SqlExecutor|null $sql Executor shared by storage and the SQL lock
     * @param Lock|null $lock Explicit strategy or default connection-owned lock
     */
    public function __construct(
        ?SqlExecutor $sql = null,
        ?Lock $lock = null
    ) {
        if ($sql === null) {
            $sql = new SqlExecutor();
        }
        parent::__construct($sql);
        $this->lock = $lock ?? new SqlLock($sql, 'framework-session:');
    }

    /**
     * Establishes exclusive ownership through the selected locking strategy
     *
     * @param string $identity Identity protected by this session handler
     * @throws \Throwable If exclusive ownership cannot be established
     */
    public function acquire(string $identity): void
    {
        $this->lock->acquire($identity);
    }

    /**
     * Releases ownership through the selected locking strategy
     *
     * @throws \Throwable If the owned lock cannot be released
     */
    public function release(): void
    {
        $this->lock->release();
    }

    /**
     * Acquires ownership before reading and retains it until session close
     *
     * @param string $sessionID Session identifier supplied by PHP
     * @return string|false Stored bytes or an empty string for a new session
     * @throws \Throwable If ownership, storage access or validation fails
     */
    public function read(string $sessionID): string|false
    {
        $this->acquire($sessionID);
        return parent::read($sessionID);
    }

    /**
     * Requires ownership before replacing the session payload
     *
     * @param string $sessionID Session identifier supplied by PHP
     * @param string $sessionData Serialized session bytes
     * @return bool True after the payload has been persisted
     * @throws \Throwable If ownership or persistence fails
     */
    public function write(string $sessionID, string $sessionData): bool
    {
        $this->acquire($sessionID);
        return parent::write($sessionID, $sessionData);
    }

    /**
     * Requires ownership before deleting the session payload
     *
     * @param string $sessionID Session identifier supplied by PHP
     * @return bool True after the deletion operation completed
     * @throws \Throwable If ownership or deletion fails
     */
    public function destroy(string $sessionID): bool
    {
        $this->acquire($sessionID);
        return parent::destroy($sessionID);
    }

    /**
     * Releases ownership when the session lifecycle completes
     *
     * @return bool True after successful release and storage cleanup
     * @throws \Throwable If the owned lock cannot be released
     */
    public function close(): bool
    {
        $this->release();
        return parent::close();
    }

    /**
     * Attempts ownership release before the handler leaves scope
     */
    public function __destruct()
    {
        try {
            $this->close();
        } catch (\Throwable) {
            // The lock strategy also owns its shutdown recovery behavior.
        }
    }
}
