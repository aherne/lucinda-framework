<?php
use Lucinda\NoSQL\ConnectionSingleton;
use Lucinda\NoSQL\OperationFailedException;

/**
 * Session handler for distributed projects that works with NoSQL key value stores (eg: Redis) instead of disk
 */
class NoSQLSessionHandler implements \SessionHandlerInterface
{    
    /**
     * {@inheritDoc}
     * @see SessionHandlerInterface::read()
     */
    public function read(string $session_id): string
    {        
        if (ConnectionSingleton::getInstance()->contains($session_id)) {
            try {
                return ConnectionSingleton::getInstance()->get($session_id);
            } catch (OperationFailedException $e) {
                return "";
            }
        } else {
            return "";
        }
    }

    /**
     * {@inheritDoc}
     * @see SessionHandlerInterface::destroy()
     */
    public function destroy(string $session_id): bool
    {
        if (ConnectionSingleton::getInstance()->contains($session_id)) {
            try {
                ConnectionSingleton::getInstance()->delete($session_id);
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
     * @see SessionHandlerInterface::gc()
     */
    public function gc(int $maxlifetime): bool
    {
        return true;
    }

    /**
     * {@inheritDoc}
     * @see SessionHandlerInterface::close()
     */
    public function close(): bool
    {
        return true;
    }

    /**
     * {@inheritDoc}
     * @see SessionHandlerInterface::write()
     */
    public function write(string $session_id, string $session_data): bool
    {                
        try {
            ConnectionSingleton::getInstance()->set($session_id, $session_data, ini_get('session.gc_maxlifetime'));
            return true;
        } catch (OperationFailedException $e) {
            return false;
        }        
    }

    /**
     * {@inheritDoc}
     * @see SessionHandlerInterface::open()
     */
    public function open(string $save_path, string $session_name): bool
    {
        return true;
    }
}

