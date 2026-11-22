<?php

namespace Lucinda\Project\Persistence\Queries\Migrations\FindMigrationTable;

/**
 * Finds migration storage through the MySQL schema catalog
 *
 * @internal
 */
final class MySQL extends AbstractQuery
{
    /**
     * Identifies the table metadata source for this dialect
     *
     * @return string Catalog table used by the generated selection
     */
    protected function getTable(): string
    {
        return 'information_schema.tables';
    }

    /**
     * Restricts discovery to the migration table in the selected schema
     *
     * @throws \Lucinda\Query\Exception If a condition definition is invalid
     */
    protected function setWhere(): void
    {
        $this->statement->where([
            'table_schema' => 'DATABASE()',
            'table_name' => ':name',
        ]);
    }
}
