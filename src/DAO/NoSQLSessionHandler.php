<?php

namespace Lucinda\Project\DAO;

use Lucinda\NoSQL\Driver;
use Lucinda\NoSQL\OperationFailedException;

/**
 * Session handler for distributed projects that works with NoSQL key value stores (eg: Redis) instead of disk
 */
class NoSQLSessionHandler implements \SessionHandlerInterface
{
    public const DRIVER_NAME = "";
    private Driver $connection;

    /**
     * Sets up DB connection
     */
    public function __construct()
    {
        $this->connection = \NoSQL(self::DRIVER_NAME);
    }

    /**
     * {@inheritDoc}
     *
     * @see \SessionHandlerInterface::write()
     */
    public function write(string $sessionID, string $sessionData): bool
    {
        try {
            $this->connection->set($sessionID, $sessionData, (int) ini_get('session.gc_maxlifetime'));
            return true;
        } catch (OperationFailedException) {
            return false;
        }
    }

    /**
     * {@inheritDoc}
     *
     * @see \SessionHandlerInterface::read()
     */
    public function read(string $sessionID): string|false
    {
        if ($this->connection->contains($sessionID)) {
            try {
                return $this->connection->get($sessionID);
            } catch (OperationFailedException) {
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * {@inheritDoc}
     *
     * @see \SessionHandlerInterface::destroy()
     */
    public function destroy(string $sessionID): bool
    {
        if ($this->connection->contains($sessionID)) {
            try {
                $this->connection->delete($sessionID);
                return true;
            } catch (OperationFailedException) {
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * {@inheritDoc}
     *
     * @see \SessionHandlerInterface::gc()
     */
    public function gc(int $maxlifetime): int|false
    {
        return 1;
    }

    /**
     * {@inheritDoc}
     *
     * @see \SessionHandlerInterface::open()
     */
    public function open(string $savePath, string $sessionName): bool
    {
        return true;
    }

    /**
     * {@inheritDoc}
     *
     * @see \SessionHandlerInterface::close()
     */
    public function close(): bool
    {
        return true;
    }
}
