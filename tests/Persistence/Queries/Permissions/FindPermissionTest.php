<?php

namespace Test\Lucinda\Project\Persistence\Queries\Permissions;

use Lucinda\UnitTest\Result;
use Lucinda\UnitTest\Validator;
use Lucinda\Project\Persistence\Queries;
use Lucinda\Framework\Persistence;
use Test\Lucinda\Project\Support\QueryContract;

/** Verifies the FindPermission SQL operation contract. */
final class FindPermissionTest
{
    /**
     * Preserves predicates, atomic writes and typed hostile-input bindings
     *
     * @return Result[] Observed behavior assertions
     */
    public function testSqlAndBindings(): array
    {
        $query = new Queries\Permissions\FindPermission(
            17,
            17,
            'PATCH',
            true,
        );
        $contract = new QueryContract(
            $query,
            'Persistence/Queries/Permissions/FindPermission'
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
            new Queries\Permissions\FindPermission(
                'invalid-account',
                17,
                'PATCH',
                true,
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
