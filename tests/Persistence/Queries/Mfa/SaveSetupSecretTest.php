<?php

namespace Test\Lucinda\Project\Persistence\Queries\Mfa;

use Lucinda\UnitTest\Result;
use Lucinda\UnitTest\Validator;
use Lucinda\Project\Persistence\Queries;
use Lucinda\Framework\Persistence;
use Test\Lucinda\Project\Support\QueryContract;

/** Verifies the SaveSetupSecret SQL operation contract. */
final class SaveSetupSecretTest
{
    /**
     * Preserves predicates, atomic writes and typed hostile-input bindings
     *
     * @return Result[] Observed behavior assertions
     */
    public function testSqlAndBindings(): array
    {
        $query = new Queries\Mfa\SaveSetupSecret(
            17,
            'input-\'cipher--',
            1700000000,
            1700000060,
        );
        $contract = new QueryContract(
            $query,
            'Persistence/Queries/Mfa/SaveSetupSecret'
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
            new Queries\Mfa\SaveSetupSecret(
                'invalid-account',
                'input-\'cipher--',
                1700000000,
                1700000060,
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
