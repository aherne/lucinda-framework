<?php

namespace Lucinda\Project\Persistence\Queries\Migrations\FindMigrationTable;

use Lucinda\SQL\DataType;

/**
 * Finds migration storage through the SQLite schema catalog
 *
 * @internal
 */
final class SQLite extends AbstractQuery
{
    /**
     * Identifies the table metadata source for this dialect
     *
     * @return string Catalog table used by the generated selection
     */
    protected function getTable(): string
    {
        return 'sqlite_schema';
    }

    /**
     * Binds the table name and excludes indexes, triggers and views
     */
    protected function setParameters(): void
    {
        parent::setParameters();
        $this->parameters->set(':type', 'table', DataType::STRING);
    }

    /**
     * Restricts discovery to the migration table in the selected schema
     *
     * @throws \Lucinda\Query\Exception If a condition definition is invalid
     */
    protected function setWhere(): void
    {
        $this->statement->where([
            'type' => ':type',
            'name' => ':name',
        ]);
    }
}
