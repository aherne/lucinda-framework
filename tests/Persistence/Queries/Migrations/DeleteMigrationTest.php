<?php

namespace Test\Lucinda\Project\Persistence\Queries\Migrations;

use Lucinda\Project\Persistence\Queries;
use Test\Lucinda\Project\Support\QueryContract;

/** Verifies the DeleteMigration SQL operation contract. */
final class DeleteMigrationTest
{
    /**
     * Preserves predicates, atomic writes and typed hostile-input bindings
     *
     * @return Result[] Observed behavior assertions
     */
    public function testSqlAndBindings(): array
    {
        $query = new Queries\Migrations\DeleteMigration(
            'input-\'class--',
        );
        $contract = new QueryContract(
            $query,
            'Persistence/Queries/Migrations/DeleteMigration'
        );
        return $contract->verify();
    }
}
