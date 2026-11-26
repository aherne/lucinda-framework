<?php

namespace Test\Lucinda\Project\Persistence\Queries\Approvals\RequestApproval;

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
        $query = new Queries\Approvals\RequestApproval\SQLite(
            'input-\'vendor--',
            'input-\'remote--',
            'input-\'name--',
            '',
        );
        $contract = new QueryContract(
            $query,
            'Persistence/Queries/Approvals/RequestApproval/SQLite'
        );
        return $contract->verify();
    }
}
