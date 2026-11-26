<?php

namespace Test\Lucinda\Project\Persistence\Queries\States;

use Lucinda\Project\Persistence\Queries;
use Test\Lucinda\Project\Support\QueryContract;

/** Verifies the SaveState SQL operation contract. */
final class SaveStateTest
{
    /**
     * Preserves predicates, atomic writes and typed hostile-input bindings
     *
     * @return Result[] Observed behavior assertions
     */
    public function testSqlAndBindings(): array
    {
        $query = new Queries\States\SaveState(
            'input-\'state--',
            'input-\'vendor--',
            1700000060,
            'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa',
        );
        $contract = new QueryContract(
            $query,
            'Persistence/Queries/States/SaveState'
        );
        return $contract->verify();
    }
}
