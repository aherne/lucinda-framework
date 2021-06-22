<?php
namespace Lucinda\Project\DAO;

use Lucinda\SQL\ConnectionSingleton;
use Lucinda\Migration\Status;

/**
 * Saves migration progress in a "migrations" SQL table (created beforehand). Create table statement if MySQL:
 *
    CREATE TABLE migrations
    (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    class_name VARCHAR(255) NOT NULL,
    is_successful BOOLEAN NOT NULL DEFAULT TRUE,
    date TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY(id),
    UNIQUE(class_name)
    ) Engine=INNODB
 *
 */
class SQLMigrationCache implements \Lucinda\Migration\Cache
{
    public const TABLE_NAME = "migrations";
    private $connection;

    /**
     * Sets table name
     *
     * @param string $tableName
     */
    public function __construct()
    {
        $this->connection = ConnectionSingleton::getInstance();
    }

    /**
     * {@inheritDoc}
     * @see \Lucinda\Migration\Cache::exists()
     */
    public function exists(): bool
    {
        // assumes table was already created
        return true;
    }

    /**
     * {@inheritDoc}
     * @see \Lucinda\Migration\Cache::create()
     */
    public function create(): void
    {
        // assumes table was already created
    }

    /**
     * {@inheritDoc}
     * @see \Lucinda\Migration\Cache::add()
     */
    public function add(string $className, int $statusCode): void
    {
        $isSuccessful = ($statusCode==\Lucinda\Migration\Status::PASSED ? 1 : 0);

        $resultSet = $this->connection->statement()->execute("
        UPDATE ".self::TABLE_NAME." 
        SET is_successful=".$isSuccessful.", date='".date("Y-m-d H:i:s")."' 
        WHERE class_name='".$className."'");
        if ($resultSet->getAffectedRows() == 0) {
            $this->connection->statement()->execute("
            INSERT INTO ".self::TABLE_NAME." (is_successful, class_name) VALUES
            (".$isSuccessful.", '".$className."')");
        }
    }

    /**
     * {@inheritDoc}
     * @see \Lucinda\Migration\Cache::read()
     */
    public function read(): array
    {
        $resultSet = $this->connection->statement()->execute("
        SELECT class_name, is_successful
        FROM ".self::TABLE_NAME);
        $output = [];
        while ($row = $resultSet->toRow()) {
            $output[$row["class_name"]] = ($row["is_successful"] ? Status::PASSED : Status::FAILED);
        }
        return $output;
    }

    /**
     * {@inheritDoc}
     * @see \Lucinda\Migration\Cache::remove()
     */
    public function remove(string $className): void
    {
        $this->connection->statement()->execute("
        DELETE FROM ".self::TABLE_NAME." 
        WHERE class_name='".$className."'");
    }
}
