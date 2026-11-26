<?php

namespace Test\Lucinda\Project\Persistence\Queries\Migrations\FindMigrationTable;

use Lucinda\Project\Persistence\Queries;
use Test\Lucinda\Project\Support\QueryContract;

/** Verifies the SQLite SQL operation contract. */
final class SQLiteTest
{
    /**
     * Preserves predicates, atomic writes and typed hostile-input bindings
     *
     * @return Result[] Observed behavior assertions
     */
    public function testSqlAndBindings(): array
    {
        $query = new Queries\Migrations\FindMigrationTable\SQLite(
        );
        $contract = new QueryContract(
            $query,
            'Persistence/Queries/Migrations/FindMigrationTable/SQLite'
        );
        return $contract->verify();
    }
}
