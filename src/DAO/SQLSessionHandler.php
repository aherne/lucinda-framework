<?php

namespace Lucinda\Project\DAO;

/**
 * Session handler for that relies on a "sessions" SQL table (created beforehand).
 */
class SQLSessionHandler implements \SessionHandlerInterface
{
    public const TABLE_NAME = "sessions";

    /**
     * {@inheritDoc}
     * @see \SessionHandlerInterface::write()
     */
    public function write(string $sessionID, string $sessionData): bool
    {
        $expiration = ini_get('session.gc_maxlifetime');
        SQL("
        INSERT INTO ".self::TABLE_NAME." (id, value, expires) VALUES
        (:id, :value, :expires)", [":id"=>$sessionID, ":value"=>$sessionData, ":expires"=>time()+$expiration]);
        return true;
    }

    /**
     * {@inheritDoc}
     * @see \SessionHandlerInterface::read()
     */
    public function read(string $sessionID): string|false
    {
        $value = SQL("
        SELECT value FROM ".self::TABLE_NAME." 
        WHERE id = :id AND expires > :current_time", [":id"=>$sessionID, ":current_time"=>time()])->toValue();
        return ($value ? $value : "");
    }

    /**
     * {@inheritDoc}
     * @see \SessionHandlerInterface::destroy()
     */
    public function destroy(string $sessionID): bool
    {
        $affectedRows = SQL("
        DELETE FROM ".self::TABLE_NAME." 
        WHERE id = :id", [":id"=>$sessionID])->getAffectedRows();
        return ($affectedRows>0);
    }

    /**
     * {@inheritDoc}
     * @see \SessionHandlerInterface::open()
     */
    public function open(string $savePath, string $sessionName): bool
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

    /**
     * {@inheritDoc}
     * @see \SessionHandlerInterface::gc()
     */
    public function gc(int $maxlifetime): int|false
    {
        $affectedRows = (int) SQL("
        DELETE FROM ".self::TABLE_NAME."
        WHERE expires < :current_time", [":current_time"=>time()])->getAffectedRows();
        var_dump($affectedRows);
        return $affectedRows;
    }
}
