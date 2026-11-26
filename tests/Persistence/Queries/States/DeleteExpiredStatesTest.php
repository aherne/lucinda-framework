<?php

namespace Test\Lucinda\Project\Persistence\Queries\States;

use Lucinda\UnitTest\Result;
use Lucinda\UnitTest\Validator;
use Lucinda\Project\Persistence\Queries;
use Test\Lucinda\Project\Support\QueryContract;

/** Verifies the DeleteExpiredStates SQL operation contract. */
final class DeleteExpiredStatesTest
{
    /**
     * Preserves predicates, atomic writes and typed hostile-input bindings
     *
     * @return Result[] Observed behavior assertions
     */
    public function testSqlAndBindings(): array
    {
        $query = new Queries\States\DeleteExpiredStates(
            1700000000,
            'input-\'state--',
            'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa',
        );
        $contract = new QueryContract(
            $query,
            'Persistence/Queries/States/DeleteExpiredStates'
        );
        return $contract->verify();
    }

    /**
     * Allows expiration maintenance without a browser proof
     *
     * @return Result[] Observed behavior assertions
     */
    public function testUnscopedMaintenance(): array
    {
        $results = [];
        $query = new Queries\States\DeleteExpiredStates(1700000000);
        $results[] = (new Validator\Arrays(
            array_keys($query->getParameters())
        ))->assertIdentical(
            [":now"],
            "Maintenance deletion has no browser-state restriction"
        );
        $results[] = (new Validator\Booleans(
            !str_contains((string) $query->getSql(), ":state")
        ))->assertTrue(
            "Expired-state maintenance applies to all abandoned attempts"
        );
        return $results;
    }
}
