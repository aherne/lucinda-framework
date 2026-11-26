<?php

namespace Test\Lucinda\Project\Persistence\Queries\Approvals;

use Lucinda\Project\Persistence\Queries;
use Test\Lucinda\Project\Support\QueryContract;

/** Verifies the FindApproval SQL operation contract. */
final class FindApprovalTest
{
    /**
     * Preserves predicates, atomic writes and typed hostile-input bindings
     *
     * @return Result[] Observed behavior assertions
     */
    public function testSqlAndBindings(): array
    {
        $query = new Queries\Approvals\FindApproval(
            'input-\'vendor--',
            'input-\'remote--',
        );
        $contract = new QueryContract(
            $query,
            'Persistence/Queries/Approvals/FindApproval'
        );
        return $contract->verify();
    }
}
