<?php

namespace Test\Lucinda\Project\Persistence\Queries\Approvals;

use Lucinda\UnitTest\Result;
use Lucinda\UnitTest\Validator;
use Lucinda\Project\Persistence\Queries;
use Lucinda\Framework\Persistence;
use Test\Lucinda\Project\Support\QueryContract;

/** Verifies the RequestApproval SQL operation contract. */
final class RequestApprovalTest
{
    /**
     * Preserves predicates, atomic writes and typed hostile-input bindings
     *
     * @return Result[] Observed behavior assertions
     */
    private function testSqlAndBindings(): array
    {
        $query = new Queries\Approvals\RequestApproval(
            Persistence\SqlVendor::MySQL,
            'input-\'vendor--',
            'input-\'remote--',
            'input-\'name--',
            '',
        );
        $contract = new QueryContract(
            $query,
            'Persistence/Queries/Approvals/RequestApproval'
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
            $query = new Queries\Approvals\RequestApproval(
                $vendor,
                'input-\'vendor--',
                'input-\'remote--',
                'input-\'name--',
                '',
            );
            $contract = new QueryContract(
                $query,
                'Persistence/Queries/Approvals/RequestApproval/' . $vendor->name
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
