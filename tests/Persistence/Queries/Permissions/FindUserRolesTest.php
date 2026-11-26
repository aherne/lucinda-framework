<?php

namespace Test\Lucinda\Project\Persistence\Queries\Permissions;

use Lucinda\UnitTest\Result;
use Lucinda\UnitTest\Validator;
use Lucinda\Project\Persistence\Queries;
use Lucinda\Framework\Persistence;
use Test\Lucinda\Project\Support\QueryContract;

/** Verifies the FindUserRoles SQL operation contract. */
final class FindUserRolesTest
{
    /**
     * Preserves predicates, atomic writes and typed hostile-input bindings
     *
     * @return Result[] Observed behavior assertions
     */
    public function testSqlAndBindings(): array
    {
        $query = new Queries\Permissions\FindUserRoles(
            17,
        );
        $contract = new QueryContract(
            $query,
            'Persistence/Queries/Permissions/FindUserRoles'
        );
        return $contract->verify();
    }

    /**
     * Rejects malformed identifiers before execution
     *
     * @return Result[] Observed behavior assertions
     */
    public function testRejectsMalformedAccount(): array
    {
        $results = [];
        $rejected = false;
        try {
            new Queries\Permissions\FindUserRoles(
                'invalid-account',
            );
        } catch (Persistence\Queries\ValidationException | \TypeError) {
            $rejected = true;
        }
        $results[] = (new Validator\Booleans(
            $rejected
        ))->assertTrue(
            "Malformed account IDs cannot become SQL expressions"
        );
        return $results;
    }
}
