<?php

namespace Test\Lucinda\Project\Persistence\Queries\Accounts;

use Lucinda\Project\Persistence\Queries;
use Test\Lucinda\Project\Support\QueryContract;

/** Verifies the FindCredentials SQL operation contract. */
final class FindCredentialsTest
{
    /**
     * Preserves predicates, atomic writes and typed hostile-input bindings
     *
     * @return Result[] Observed behavior assertions
     */
    public function testSqlAndBindings(): array
    {
        $query = new Queries\Accounts\FindCredentials(
            'input-\'username--',
        );
        $contract = new QueryContract(
            $query,
            'Persistence/Queries/Accounts/FindCredentials'
        );
        return $contract->verify();
    }
}
