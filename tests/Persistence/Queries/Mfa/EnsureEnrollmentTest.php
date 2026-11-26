<?php

namespace Test\Lucinda\Project\Persistence\Queries\Mfa;

use Lucinda\UnitTest\Result;
use Lucinda\UnitTest\Validator;
use Lucinda\Project\Persistence\Queries;
use Lucinda\Framework\Persistence;
use Test\Lucinda\Project\Support\QueryContract;

/** Verifies the EnsureEnrollment SQL operation contract. */
final class EnsureEnrollmentTest
{
    /**
     * Preserves predicates, atomic writes and typed hostile-input bindings
     *
     * @return Result[] Observed behavior assertions
     */
    private function testSqlAndBindings(): array
    {
        $query = new Queries\Mfa\EnsureEnrollment(
            Persistence\SqlVendor::MySQL,
            17,
        );
        $contract = new QueryContract(
            $query,
            'Persistence/Queries/Mfa/EnsureEnrollment'
        );
        return $contract->verify();
    }

    /**
     * Selects the matching native SQL operation for every supported vendor
     *
     * @return Result[] Observed behavior assertions
     */
    public function testDialectSelection(): array
    {
        $results = [];
        foreach (
            [
                Persistence\SqlVendor::MySQL,
                Persistence\SqlVendor::PostgreSQL,
                Persistence\SqlVendor::SQLite,
            ] as $vendor
        ) {
            $query = new Queries\Mfa\EnsureEnrollment(
                $vendor,
                17,
            );
            $contract = new QueryContract(
                $query,
                'Persistence/Queries/Mfa/EnsureEnrollment/' . $vendor->name
            );
            foreach ($contract->verify() as $result) {
                $results[] = (new Validator\Booleans(
                    $result->hasPassed()
                ))->assertTrue(
                    $vendor->name . ": " . $result->getMessage()
                );
            }
        }
        return $results;
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
            new Queries\Mfa\EnsureEnrollment(
                Persistence\SqlVendor::MySQL,
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
    /**
     * Verifies getSql through the complete application contract
     *
     * @return Result[] Assertions from the shared contract scenario
     */
    public function getSql(): array
    {
        return $this->testSqlAndBindings();
    }

    /**
     * Verifies getParameters through the complete application contract
     *
     * @return Result[] Assertions from the shared contract scenario
     */
    public function getParameters(): array
    {
        return $this->testSqlAndBindings();
    }

    /**
     * Verifies isSelection through the complete application contract
     *
     * @return Result[] Assertions from the shared contract scenario
     */
    public function isSelection(): array
    {
        return $this->testSqlAndBindings();
    }
}
