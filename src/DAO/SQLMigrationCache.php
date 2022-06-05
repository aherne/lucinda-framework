<?php

namespace Lucinda\Project\DAO;

use Lucinda\Migration\Status;

/**
 * Saves migration progress in a "migrations" SQL table (created beforehand)
 */
class SQLMigrationCache implements \Lucinda\Migration\Cache
{
    public const DRIVER_NAME = "";
    public const TABLE_NAME = "migrations";

    /**
     * {@inheritDoc}
     *
     * @see \Lucinda\Migration\Cache::exists()
     */
    public function exists(): bool
    {
        // assumes table was already created
        return true;
    }

    /**
     * {@inheritDoc}
     *
     * @see \Lucinda\Migration\Cache::create()
     */
    public function create(): void
    {
        // assumes table was already created
    }

    /**
     * {@inheritDoc}
     *
     * @see \Lucinda\Migration\Cache::add()
     */
    public function add(string $className, Status $statusCode): void
    {
        $isSuccessful = ($statusCode==\Lucinda\Migration\Status::PASSED ? 1 : 0);

        $resultSet = \SQL(
            "
            UPDATE ".self::TABLE_NAME." 
            SET is_successful=:is_successful, date=:date 
            WHERE class_name=:class_name
        ",
            [
            ":date"=>date("Y-m-d H:i:s"),
            ":is_successful"=>$isSuccessful,
            ":class_name"=>$className
            ],
            self::DRIVER_NAME
        );
        if ($resultSet->getAffectedRows() == 0) {
            \SQL(
                "
                INSERT INTO ".self::TABLE_NAME." (is_successful, class_name) VALUES
                (:is_successful, :class_name)
            ",
                [
                ":is_successful"=>$isSuccessful,
                ":class_name"=>$className
                ],
                self::DRIVER_NAME
            );
        }
    }

    /**
     * {@inheritDoc}
     *
     * @see \Lucinda\Migration\Cache::read()
     */
    public function read(): array
    {
        $resultSet = \SQL(
            "
        SELECT class_name, is_successful
        FROM ".self::TABLE_NAME,
            [],
            self::DRIVER_NAME
        );
        $output = [];
        while ($row = $resultSet->toRow()) {
            $output[$row["class_name"]] = ($row["is_successful"] ? Status::PASSED : Status::FAILED);
        }
        return $output;
    }

    /**
     * {@inheritDoc}
     *
     * @see \Lucinda\Migration\Cache::remove()
     */
    public function remove(string $className): void
    {
        \SQL(
            "
            DELETE FROM ".self::TABLE_NAME." 
            WHERE class_name=:class_name
        ",
            [
            ":class_name"=>$className
            ],
            self::DRIVER_NAME
        );
    }
}
