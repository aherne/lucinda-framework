<?php

namespace Test\Lucinda\Project\Persistence\Queries\Approvals\RequestApproval;

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
        $query = new Queries\Approvals\RequestApproval\PostgreSQL(
            'input-\'vendor--',
            'input-\'remote--',
            'input-\'name--',
            '',
        );
        $contract = new QueryContract(
            $query,
            'Persistence/Queries/Approvals/RequestApproval/PostgreSQL'
        );
        return $contract->verify();
    }
}
