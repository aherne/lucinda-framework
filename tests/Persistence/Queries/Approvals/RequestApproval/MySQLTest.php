<?php

namespace Test\Lucinda\Project\Persistence\Queries\Approvals\RequestApproval;

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
        $query = new Queries\Approvals\RequestApproval\MySQL(
            'input-\'vendor--',
            'input-\'remote--',
            'input-\'name--',
            '',
        );
        $contract = new QueryContract(
            $query,
            'Persistence/Queries/Approvals/RequestApproval/MySQL'
        );
        return $contract->verify();
    }
}
