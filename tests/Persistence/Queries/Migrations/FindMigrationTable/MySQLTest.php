<?php

namespace Test\Lucinda\Project\Persistence\Queries\Migrations\FindMigrationTable;

use Lucinda\Project\Persistence\Queries;
use Test\Lucinda\Project\Support\QueryContract;

/** Verifies the MySQL SQL operation contract. */
final class MySQLTest
{
    /**
     * Preserves predicates, atomic writes and typed hostile-input bindings
     *
     * @return Result[] Observed behavior assertions
     */
    public function testSqlAndBindings(): array
    {
        $query = new Queries\Migrations\FindMigrationTable\MySQL(
        );
        $contract = new QueryContract(
            $query,
            'Persistence/Queries/Migrations/FindMigrationTable/MySQL'
        );
        return $contract->verify();
    }
}
