<?php

namespace Test\Lucinda\Project\Persistence\Queries\Accounts;

use Lucinda\Project\Persistence\Queries;
use Test\Lucinda\Project\Support\QueryContract;

/** Verifies the FindIdentity SQL operation contract. */
final class FindIdentityTest
{
    /**
     * Preserves predicates, atomic writes and typed hostile-input bindings
     *
     * @return Result[] Observed behavior assertions
     */
    public function testSqlAndBindings(): array
    {
        $query = new Queries\Accounts\FindIdentity(
            'input-\'vendor--',
            'input-\'remote--',
        );
        $contract = new QueryContract(
            $query,
            'Persistence/Queries/Accounts/FindIdentity'
        );
        return $contract->verify();
    }
}
