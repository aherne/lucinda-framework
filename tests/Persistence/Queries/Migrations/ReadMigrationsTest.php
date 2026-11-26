<?php

namespace Test\Lucinda\Project\Persistence\Queries\Migrations;

use Lucinda\Project\Persistence\Queries;
use Test\Lucinda\Project\Support\QueryContract;

/** Verifies the ReadMigrations SQL operation contract. */
final class ReadMigrationsTest
{
    /**
     * Preserves predicates, atomic writes and typed hostile-input bindings
     *
     * @return Result[] Observed behavior assertions
     */
    public function testSqlAndBindings(): array
    {
        $query = new Queries\Migrations\ReadMigrations(
        );
        $contract = new QueryContract(
            $query,
            'Persistence/Queries/Migrations/ReadMigrations'
        );
        return $contract->verify();
    }
}
