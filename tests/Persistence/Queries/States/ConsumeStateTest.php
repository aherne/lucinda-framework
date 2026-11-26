<?php

namespace Test\Lucinda\Project\Persistence\Queries\States;

use Lucinda\Project\Persistence\Queries;
use Test\Lucinda\Project\Support\QueryContract;

/** Verifies the ConsumeState SQL operation contract. */
final class ConsumeStateTest
{
    /**
     * Preserves predicates, atomic writes and typed hostile-input bindings
     *
     * @return Result[] Observed behavior assertions
     */
    public function testSqlAndBindings(): array
    {
        $query = new Queries\States\ConsumeState(
            'input-\'state--',
            'input-\'vendor--',
            'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa',
            1700000000,
        );
        $contract = new QueryContract(
            $query,
            'Persistence/Queries/States/ConsumeState'
        );
        return $contract->verify();
    }
}
