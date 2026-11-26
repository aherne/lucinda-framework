<?php

namespace Test\Lucinda\Project\Persistence\Queries\Sessions;

use Lucinda\Project\Persistence\Queries;
use Test\Lucinda\Project\Support\QueryContract;

/** Verifies the DeleteSession SQL operation contract. */
final class DeleteSessionTest
{
    /**
     * Preserves predicates, atomic writes and typed hostile-input bindings
     *
     * @return Result[] Observed behavior assertions
     */
    public function testSqlAndBindings(): array
    {
        $query = new Queries\Sessions\DeleteSession(
            '0017',
        );
        $contract = new QueryContract(
            $query,
            'Persistence/Queries/Sessions/DeleteSession'
        );
        return $contract->verify();
    }
}
