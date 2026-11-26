<?php

namespace Test\Lucinda\Project\Persistence\Queries\Sessions;

use Lucinda\Project\Persistence\Queries;
use Test\Lucinda\Project\Support\QueryContract;

/** Verifies the ReadSession SQL operation contract. */
final class ReadSessionTest
{
    /**
     * Preserves predicates, atomic writes and typed hostile-input bindings
     *
     * @return Result[] Observed behavior assertions
     */
    public function testSqlAndBindings(): array
    {
        $query = new Queries\Sessions\ReadSession(
            '0017',
            1700000000,
        );
        $contract = new QueryContract(
            $query,
            'Persistence/Queries/Sessions/ReadSession'
        );
        return $contract->verify();
    }
}
