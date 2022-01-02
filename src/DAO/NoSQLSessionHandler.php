<?php
namespace Lucinda\Project\DAO;

use Lucinda\NoSQL\ConnectionSingleton;
use Lucinda\NoSQL\Driver;
use Lucinda\NoSQL\OperationFailedException;

/**
 * Session handler for distributed projects that works with NoSQL key value stores (eg: Redis) instead of disk
 */
class NoSQLSessionHandler implements \SessionHandlerInterface
{
    private Driver $connection;

    /**
     * Sets up DB connection
     */
    public function __construct()
    {
        $this->connection = ConnectionSingleton::getInstance();
    }

    /**
     * {@inheritDoc}
     * @see \SessionHandlerInterface::write()
     */
    public function write(string $session_id, string $session_data): bool
    {
        try {
            $this->connection->set($session_id, $session_data, ini_get('session.gc_maxlifetime'));
            return true;
        } catch (OperationFailedException $e) {
            return false;
        }
    }

    /**
     * {@inheritDoc}
     * @see \SessionHandlerInterface::read()
     */
    public function read(string $session_id): string|false
    {
        if ($this->connection->contains($session_id)) {
            try {
                return $this->connection->get($session_id);
            } catch (OperationFailedException $e) {
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * {@inheritDoc}
     * @see \SessionHandlerInterface::destroy()
     */
    public function destroy(string $session_id): bool
    {
        if ($this->connection->contains($session_id)) {
            try {
                $this->connection->delete($session_id);
                return true;
            } catch (OperationFailedException $e) {
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * {@inheritDoc}
     * @see \SessionHandlerInterface::gc()
     */
    public function gc(int $maxlifetime): int|false
    {
        return 1;
    }

    /**
     * {@inheritDoc}
     * @see \SessionHandlerInterface::open()
     */
    public function open(string $save_path, string $session_name): bool
    {
        return true;
    }

    /**
     * {@inheritDoc}
     * @see \SessionHandlerInterface::close()
     */
    public function close(): bool
    {
        return true;
    }
}
