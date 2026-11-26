<?php

namespace Test\Lucinda\Project\Persistence\Queries\Migrations\SaveMigration;

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
        $query = new Queries\Migrations\SaveMigration\SQLite(
            'input-\'class--',
            2,
        );
        $contract = new QueryContract(
            $query,
            'Persistence/Queries/Migrations/SaveMigration/SQLite'
        );
        return $contract->verify();
    }
}
