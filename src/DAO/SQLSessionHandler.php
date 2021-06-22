<?php
namespace Lucinda\Project\DAO;

/**
 * Session handler for that relies on a "sessions" SQL table (created beforehand). Create table statement if MySQL:
 *
    CREATE TABLE sessions
    (
    id VARCHAR(50) NOT NULL,
    value BLOB NOT NULL,
    date TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    expires INT UNSIGNED NOT NULL DEFAULT 0,
    PRIMARY KEY(id),
    KEY(expires)
    ) Engine=INNODB
 *
 */
class SQLSessionHandler implements \SessionHandlerInterface
{
    public const TABLE_NAME = "sessions";

    /**
     * {@inheritDoc}
     * @see \SessionHandlerInterface::write()
     */
    public function write($session_id, $session_data)
    {
        $expiration = ini_get('session.gc_maxlifetime');
        SQL("
        INSERT INTO ".self::TABLE_NAME." (id, value, expires) VALUES
        (:id, :value, :expires)", [":id"=>$session_id, ":value"=>$session_data, ":expires"=>time()+$expiration]);
        return true;
    }

    /**
     * {@inheritDoc}
     * @see \SessionHandlerInterface::read()
     */
    public function read($session_id)
    {
        $value = SQL("
        SELECT value FROM ".self::TABLE_NAME." 
        WHERE id = :id AND expires > :current_time", [":id"=>$session_id, ":current_time"=>time()])->toValue();
        return ($value ? $value : "");
    }

    /**
     * {@inheritDoc}
     * @see \SessionHandlerInterface::destroy()
     */
    public function destroy($session_id)
    {
        $affectedRows = SQL("
        DELETE FROM ".self::TABLE_NAME." 
        WHERE id = :id", [":id"=>$session_id])->getAffectedRows();
        return ($affectedRows>0);
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

    /**
     * {@inheritDoc}
     * @see \SessionHandlerInterface::gc()
     */
    public function gc($maxlifetime)
    {
        SQL("
        DELETE FROM ".self::TABLE_NAME."
        WHERE expires < :current_time", [":current_time"=>time()])->getAffectedRows();
        return true;
    }
}
