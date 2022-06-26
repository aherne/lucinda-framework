<?php

namespace Lucinda\Project\DAO;

/**
 * Session handler for that relies on a "sessions" SQL table (created beforehand).
 */
class SQLSessionHandler implements \SessionHandlerInterface
{
    public const DRIVER_NAME = "";
    public const TABLE_NAME = "sessions";

    /**
     * {@inheritDoc}
     *
     * @see \SessionHandlerInterface::write()
     */
    public function write($sessionID, $sessionData)
    {
        $expiration = ini_get('session.gc_maxlifetime');
        SQL(
            "
            INSERT INTO ".self::TABLE_NAME." (id, value, expires) VALUES
            (:id, :value, :expires)
        ",
            [
            ":id"=>$sessionID,
            ":value"=>$sessionData,
            ":expires"=>time()+$expiration
            ],
            self::DRIVER_NAME
        );
        return true;
    }

    /**
     * {@inheritDoc}
     *
     * @see \SessionHandlerInterface::read()
     */
    public function read($sessionID)
    {
        $value = SQL(
            "
            SELECT value FROM ".self::TABLE_NAME." 
            WHERE id = :id AND expires > :current_time
        ",
            [
            ":id"=>$sessionID,
            ":current_time"=>time()
            ],
            self::DRIVER_NAME
        )->toValue();
        return ($value ? $value : "");
    }

    /**
     * {@inheritDoc}
     *
     * @see \SessionHandlerInterface::destroy()
     */
    public function destroy($sessionID)
    {
        $affectedRows = SQL(
            "
            DELETE FROM ".self::TABLE_NAME." 
            WHERE id = :id
        ",
            [
            ":id"=>$sessionID
            ],
            self::DRIVER_NAME
        )->getAffectedRows();
        return ($affectedRows>0);
    }

    /**
     * {@inheritDoc}
     *
     * @see \SessionHandlerInterface::open()
     */
    public function open($savePath, $sessionName)
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

    /**
     * {@inheritDoc}
     *
     * @see \SessionHandlerInterface::gc()
     */
    public function gc($maxlifetime)
    {
        return (int) SQL(
            "
            DELETE FROM ".self::TABLE_NAME."
            WHERE expires < :current_time
        ",
            [
            ":current_time"=>time()
            ],
            self::DRIVER_NAME
        )->getAffectedRows();
    }
}
