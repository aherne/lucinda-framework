<?php

namespace Test\Lucinda\Project\Persistence\Queries\Accounts;

use Lucinda\UnitTest\Result;
use Lucinda\UnitTest\Validator;
use Lucinda\Project\Persistence\Queries;
use Lucinda\Framework\Persistence;
use Test\Lucinda\Project\Support\QueryContract;

/** Verifies the UpdatePasswordHash SQL operation contract. */
final class UpdatePasswordHashTest
{
    /**
     * Preserves predicates, atomic writes and typed hostile-input bindings
     *
     * @return Result[] Observed behavior assertions
     */
    public function testSqlAndBindings(): array
    {
        $query = new Queries\Accounts\UpdatePasswordHash(
            17,
            'input-\'oldHash--',
            'input-\'newHash--',
        );
        $contract = new QueryContract(
            $query,
            'Persistence/Queries/Accounts/UpdatePasswordHash'
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
            new Queries\Accounts\UpdatePasswordHash(
                'invalid-account',
                'input-\'oldHash--',
                'input-\'newHash--',
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
