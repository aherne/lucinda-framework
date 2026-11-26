<?php

namespace Test\Lucinda\Project\Persistence\Queries\Mfa;

use Lucinda\UnitTest\Result;
use Lucinda\UnitTest\Validator;
use Lucinda\Project\Persistence\Queries;
use Lucinda\Framework\Persistence;
use Test\Lucinda\Project\Support\QueryContract;

/** Verifies the ConsumeCounter SQL operation contract. */
final class ConsumeCounterTest
{
    /**
     * Preserves predicates, atomic writes and typed hostile-input bindings
     *
     * @return Result[] Observed behavior assertions
     */
    public function testSqlAndBindings(): array
    {
        $query = new Queries\Mfa\ConsumeCounter(
            17,
            123,
            1700000000,
        );
        $contract = new QueryContract(
            $query,
            'Persistence/Queries/Mfa/ConsumeCounter'
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
            new Queries\Mfa\ConsumeCounter(
                'invalid-account',
                123,
                1700000000,
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
