<?php

namespace Test\Lucinda\Project\DAO;

use Lucinda\UnitTest\Result;
use Lucinda\UnitTest\Validator;
use Lucinda\Project\DAO;
use Lucinda\Migration\Status;
use Test\Lucinda\Project\Support\RedisFixture;

/** Verifies NoSQLMigrationCache application behavior. */
final class NoSQLMigrationCacheTest
{
    /**
     * Preserves every migration status through idempotent writes and removal
     *
     * @return Result[] Observed behavior assertions
     */
    private function testLedgerLifecycle(): array
    {
        $results = [];
        $redis = new RedisFixture();
        $executor = $redis->getExecutor();
        $dao = new DAO\NoSQLMigrationCache($executor);
        $dao->create();
        $results[] = (new Validator\Booleans(
            $dao->exists() === true
        ))->assertTrue(
            'Ledger exists after initialization'
        );
        $results[] = (new Validator\Arrays(
            $dao->read()
        ))->assertIdentical(
            [],
            'Initial ledger is empty'
        );
        foreach ([Status::PENDING, Status::FAILED, Status::PASSED] as $status) {
            $dao->add('Example', $status);
            $dao->add('Example', $status);
            $results[] = (new Validator\Arrays(
                $dao->read()
            ))->assertIdentical(
                ['Example' => $status],
                'Repeated status upsert is idempotent'
            );
        }
        $dao->add('Other', Status::PENDING);
        $dao->create();
        $results[] = (new Validator\Integers(
            count($dao->read())
        ))->assertEquals(
            2,
            'Reinitialization retains all migration results'
        );
        $dao->remove('Example');
        $dao->remove('Example');
        $results[] = (new Validator\Arrays(
            $dao->read()
        ))->assertIdentical(
            ['Other' => Status::PENDING],
            'Removal preserves unrelated statuses'
        );
        return $results;
    }
    /**
     * Verifies exists through the complete application contract
     *
     * @return Result[] Assertions from the shared contract scenario
     */
    public function exists(): array
    {
        return $this->testLedgerLifecycle();
    }

    /**
     * Verifies create through the complete application contract
     *
     * @return Result[] Assertions from the shared contract scenario
     */
    public function create(): array
    {
        return $this->testLedgerLifecycle();
    }

    /**
     * Verifies add through the complete application contract
     *
     * @return Result[] Assertions from the shared contract scenario
     */
    public function add(): array
    {
        return $this->testLedgerLifecycle();
    }

    /**
     * Verifies read through the complete application contract
     *
     * @return Result[] Assertions from the shared contract scenario
     */
    public function read(): array
    {
        return $this->testLedgerLifecycle();
    }

    /**
     * Verifies remove through the complete application contract
     *
     * @return Result[] Assertions from the shared contract scenario
     */
    public function remove(): array
    {
        return $this->testLedgerLifecycle();
    }
}
