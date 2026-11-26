<?php

namespace Test\Lucinda\Project\Persistence\Queries\Permissions;

use Lucinda\Project\Persistence\Queries;
use Test\Lucinda\Project\Support\QueryContract;

/** Verifies the FindPageByUrl SQL operation contract. */
final class FindPageByUrlTest
{
    /**
     * Preserves predicates, atomic writes and typed hostile-input bindings
     *
     * @return Result[] Observed behavior assertions
     */
    public function testSqlAndBindings(): array
    {
        $query = new Queries\Permissions\FindPageByUrl(
            'input-\'url--',
        );
        $contract = new QueryContract(
            $query,
            'Persistence/Queries/Permissions/FindPageByUrl'
        );
        return $contract->verify();
    }
}
