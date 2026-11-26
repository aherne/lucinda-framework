<?php

namespace Test\Lucinda\Project\Persistence\Queries\Migrations\SaveMigration;

use Lucinda\Project\Persistence\Queries;
use Test\Lucinda\Project\Support\QueryContract;

/** Verifies the PostgreSQL SQL operation contract. */
final class PostgreSQLTest
{
    /**
     * Preserves predicates, atomic writes and typed hostile-input bindings
     *
     * @return Result[] Observed behavior assertions
     */
    public function testSqlAndBindings(): array
    {
        $query = new Queries\Migrations\SaveMigration\PostgreSQL(
            'input-\'class--',
            2,
        );
        $contract = new QueryContract(
            $query,
            'Persistence/Queries/Migrations/SaveMigration/PostgreSQL'
        );
        return $contract->verify();
    }
}
