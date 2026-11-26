<?php

namespace Test\Lucinda\Project\Persistence\Queries\Sessions\WriteSession;

use Lucinda\Project\Persistence\Queries;
use Test\Lucinda\Project\Support\QueryContract;

/** Verifies the SQLite SQL operation contract. */
final class SQLiteTest
{
    /**
     * Preserves predicates, atomic writes and typed hostile-input bindings
     *
     * @return Result[] Observed behavior assertions
     */
    public function testSqlAndBindings(): array
    {
        $query = new Queries\Sessions\WriteSession\SQLite(
            '0017',
            'input-\'payload--',
            1700000060,
        );
        $contract = new QueryContract(
            $query,
            'Persistence/Queries/Sessions/WriteSession/SQLite'
        );
        return $contract->verify();
    }
}
