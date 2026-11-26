<?php

namespace Test\Lucinda\Project\DAO;

use Lucinda\UnitTest\Result;
use Lucinda\UnitTest\Validator;
use Lucinda\Project\DAO;
use Test\Lucinda\Project\Support\RedisFixture;

/** Verifies NoSQLSessionHandler application behavior. */
final class NoSQLSessionHandlerTest
{
    /**
     * Preserves binary payloads and releases its lease at close
     *
     * @return Result[] Observed behavior assertions
     */
    private function testPayloadLifecycle(): array
    {
        $results = [];
        $redis = new RedisFixture();
        $driver = $redis->getDriver();
        $executor = $redis->getExecutor();
        $dao = new DAO\NoSQLSessionHandler($executor);
        $results[] = (new Validator\Booleans(
            $dao->open('', 'session') === true
        ))->assertTrue(
            'Handler opens with its injected storage'
        );
        try {
            $results[] = (new Validator\Strings(
                $dao->read('session')
            ))->assertEquals(
                '',
                'Missing session has empty payload'
            );
            $results[] = (new Validator\Booleans(
                $dao->write('session', "a\0\xffb") === true
            ))->assertTrue(
                'Binary payload written'
            );
            $results[] = (new Validator\Strings(
                $dao->read('session')
            ))->assertEquals(
                "a\0\xffb",
                'Binary payload round trips'
            );
            $key = $executor->getKeyspace()->getKey('session', 'session');
            $before = $executor->read($key)->getValue();
            $dao->renew();
            $after = $executor->read($key)->getValue();
            $results[] = (new Validator\Arrays(
                [$after['payload_expires']]
            ))->assertIdentical(
                [$before['payload_expires']],
                'Lease renewal preserves the payload deadline'
            );
            $results[] = (new Validator\Integers(
                $dao->gc(1)
            ))->assertEquals(
                0,
                'Expiration needs no global storage scan'
            );
            $dao->destroy('session');
            $dao->destroy('session');
            $results[] = (new Validator\Strings(
                $dao->read('session')
            ))->assertEquals(
                '',
                'Repeated destruction leaves an empty session'
            );
        } finally {
            $dao->close();
        }
        $successor = new DAO\NoSQLSessionHandler($executor);
        $successor->write('session', 'successor');
        $dao->close();
        $results[] = (new Validator\Strings(
            $successor->read('session')
        ))->assertEquals(
            'successor',
            'Old close cannot delete successor ownership'
        );
        $successor->close();
        return $results;
    }

    /**
     * Rejects stale writes and renewals without affecting a successor lease
     *
     * @return Result[] Observed behavior assertions
     */
    public function testLostOwnership(): array
    {
        $results = [];
        $redis = new RedisFixture();
        $executor = $redis->getExecutor();
        $first = new DAO\NoSQLSessionHandler($executor);
        $first->write('session', 'original');
        $key = $executor->getKeyspace()->getKey('session', 'session');
        $snapshot = $executor->read($key);
        $values = $snapshot->getValue();
        $values['lease_expires'] = time() - 1;
        $executor->replace($snapshot, $values, 120);
        $second = new DAO\NoSQLSessionHandler($executor);
        try {
            $second->write('session', 'winner');
            $rejected = false;
            try {
                $first->write('session', 'stale');
            } catch (\RuntimeException) {
                $rejected = true;
            }
            $results[] = (new Validator\Booleans(
                $rejected
            ))->assertTrue(
                'Stale writer cannot overwrite the successor payload'
            );
            $rejected = false;
            try {
                $first->renew();
            } catch (\RuntimeException) {
                $rejected = true;
            }
            $results[] = (new Validator\Booleans(
                $rejected
            ))->assertTrue(
                'Lost owner cannot renew its lease'
            );
            $first->close();
            $results[] = (new Validator\Strings(
                $second->read('session')
            ))->assertEquals(
                'winner',
                'Stale release leaves successor intact'
            );
        } finally {
            $second->close();
            $first->close();
        }
        return $results;
    }
    /**
     * Verifies open through the complete application contract
     *
     * @return Result[] Assertions from the shared contract scenario
     */
    public function open(): array
    {
        return $this->testPayloadLifecycle();
    }

    /**
     * Verifies close through the complete application contract
     *
     * @return Result[] Assertions from the shared contract scenario
     */
    public function close(): array
    {
        return $this->testPayloadLifecycle();
    }

    /**
     * Verifies renew through the complete application contract
     *
     * @return Result[] Assertions from the shared contract scenario
     */
    public function renew(): array
    {
        return $this->testPayloadLifecycle();
    }

    /**
     * Verifies read through the complete application contract
     *
     * @return Result[] Assertions from the shared contract scenario
     */
    public function read(): array
    {
        return $this->testPayloadLifecycle();
    }

    /**
     * Verifies write through the complete application contract
     *
     * @return Result[] Assertions from the shared contract scenario
     */
    public function write(): array
    {
        return $this->testPayloadLifecycle();
    }

    /**
     * Verifies destroy through the complete application contract
     *
     * @return Result[] Assertions from the shared contract scenario
     */
    public function destroy(): array
    {
        return $this->testPayloadLifecycle();
    }

    /**
     * Verifies gc through the complete application contract
     *
     * @return Result[] Assertions from the shared contract scenario
     */
    public function gc(): array
    {
        return $this->testPayloadLifecycle();
    }
}
