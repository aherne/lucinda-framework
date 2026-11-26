<?php

namespace Test\Lucinda\Project\Persistence\Queries\Sessions\WriteSession;

use Lucinda\Project\Persistence\Queries;
use Test\Lucinda\Project\Support\QueryContract;

/** Verifies the MySQL SQL operation contract. */
final class MySQLTest
{
    /**
     * Preserves predicates, atomic writes and typed hostile-input bindings
     *
     * @return Result[] Observed behavior assertions
     */
    public function testSqlAndBindings(): array
    {
        $query = new Queries\Sessions\WriteSession\MySQL(
            '0017',
            'input-\'payload--',
            1700000060,
        );
        $contract = new QueryContract(
            $query,
            'Persistence/Queries/Sessions/WriteSession/MySQL'
        );
        return $contract->verify();
    }
}
