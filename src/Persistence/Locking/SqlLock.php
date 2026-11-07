<?php

namespace Lucinda\Project\Persistence\Locking;

use Lucinda\Project\Persistence\Queries\Locks\AcquireLock;
use Lucinda\Project\Persistence\Queries\Locks\ReleaseLock;
use Lucinda\Project\Persistence\SqlExecutor;

/**
 * Owns one MySQL advisory lock through the executor's retained connection
 *
 * The lock namespace belongs to the caller. Ownership is tracked separately
 * from the storage operation protected by this lock.
 *
 * @internal
 */
final class SqlLock implements Lock
{
    /** Executor retaining the MySQL connection that owns the advisory lock */
    private SqlExecutor $sql;

    /** Namespace distinguishing this caller's protected identities */
    private string $namespace;

    /** Maximum acquisition wait in seconds, zero for a nonblocking attempt */
    private int $timeout;

    /** Advisory lock name currently owned through this instance */
    private ?string $name = null;

    /**
     * Captures the connection and namespace used to establish ownership
     *
     * @param SqlExecutor $sql Executor shared with the protected operation
     * @param string $namespace Namespace for this caller's lock identities
     * @param int $timeout Maximum acquisition wait in seconds
     * @throws \InvalidArgumentException If namespace or timeout is invalid
     */
    public function __construct(
        SqlExecutor $sql,
        string $namespace,
        int $timeout = 10
    ) {
        if ($namespace === '' || $timeout < 0) {
            throw new \InvalidArgumentException('Invalid SQL lock policy');
        }
        $this->sql = $sql;
        $this->namespace = $namespace;
        $this->timeout = $timeout;
    }

    /**
     * Acquires the requested identity without reentering an already owned lock
     *
     * @param string $identity Identity protected by the coordinating caller
     * @throws \RuntimeException If the acquisition times out
     * @throws \Throwable If immediate persistence or database access fails
     */
    public function acquire(string $identity): void
    {
        $name = hash('sha256', $this->namespace . $identity);
        if ($name === $this->name) {
            return;
        }
        $this->release();
        $this->sql->requireAutocommit();
        $query = new AcquireLock($name, $this->timeout);
        $selection = $this->sql->select($query);
        $row = $selection->getFirstRow();
        if ($row === null || $row->getInteger('value') !== 1) {
            throw new \RuntimeException('SQL lock acquisition timed out');
        }
        $this->name = $name;
    }

    /**
     * Releases the advisory lock owned by this instance and connection
     *
     * @throws \RuntimeException If ownership cannot be confirmed on release
     * @throws \Throwable If the release operation fails
     */
    public function release(): void
    {
        if ($this->name === null) {
            return;
        }
        $selection = $this->sql->select(new ReleaseLock($this->name));
        $row = $selection->getFirstRow();
        if ($row === null || $row->getInteger('value') !== 1) {
            throw new \RuntimeException('SQL lock ownership was lost');
        }
        $this->name = null;
    }

    /**
     * Attempts release before the owner leaves scope
     */
    public function __destruct()
    {
        try {
            $this->release();
        } catch (\Throwable) {
            // Connection termination remains MySQL's ownership recovery path.
        }
    }
}
