<?php

namespace Test\Lucinda\Project\Persistence\Queries\Sessions;

use Lucinda\Project\Persistence\Queries;
use Test\Lucinda\Project\Support\QueryContract;

/** Verifies the DeleteExpiredSessions SQL operation contract. */
final class DeleteExpiredSessionsTest
{
    /**
     * Preserves predicates, atomic writes and typed hostile-input bindings
     *
     * @return Result[] Observed behavior assertions
     */
    public function testSqlAndBindings(): array
    {
        $query = new Queries\Sessions\DeleteExpiredSessions(
            1700000000,
        );
        $contract = new QueryContract(
            $query,
            'Persistence/Queries/Sessions/DeleteExpiredSessions'
        );
        return $contract->verify();
    }
}
