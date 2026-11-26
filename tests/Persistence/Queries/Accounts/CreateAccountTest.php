<?php

namespace Test\Lucinda\Project\Persistence\Queries\Accounts;

use Lucinda\Project\Persistence\Queries;
use Test\Lucinda\Project\Support\QueryContract;

/** Verifies the CreateAccount SQL operation contract. */
final class CreateAccountTest
{
    /**
     * Preserves predicates, atomic writes and typed hostile-input bindings
     *
     * @return Result[] Observed behavior assertions
     */
    public function testSqlAndBindings(): array
    {
        $query = new Queries\Accounts\CreateAccount(
            'input-\'name--',
            '',
        );
        $contract = new QueryContract(
            $query,
            'Persistence/Queries/Accounts/CreateAccount'
        );
        return $contract->verify();
    }
}
