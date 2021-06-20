<?php
namespace Lucinda\Project\DAO;

use Lucinda\NoSQL\ConnectionSingleton;
use Lucinda\NoSQL\OperationFailedException;

/**
 * Session handler for distributed projects that works with NoSQL key value stores (eg: Redis) instead of disk
 */
class NoSQLSessionHandler implements \SessionHandlerInterface
{
    private $connection;
    
    public function __construct()
    {
        $this->connection = ConnectionSingleton::getInstance();
    }
    
    /**
     * {@inheritDoc}
     * @see \SessionHandlerInterface::write()
     */
    public function write($session_id, $session_data)
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
    public function read($session_id)
    {
        if ($this->connection->contains($session_id)) {
            try {
                return $this->connection->get($session_id);
            } catch (OperationFailedException $e) {
                return "";
            }
        } else {
            return "";
        }
    }

    /**
     * {@inheritDoc}
     * @see \SessionHandlerInterface::destroy()
     */
    public function destroy($session_id)
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
    public function gc($maxlifetime)
    {
        return true;
    }
    
    /**
     * {@inheritDoc}
     * @see \SessionHandlerInterface::open()
     */
    public function open($save_path, $session_name)
    {
        return true;
    }

    /**
     * {@inheritDoc}
     * @see \SessionHandlerInterface::close()
     */
    public function close()
    {
        return true;
    }
}
