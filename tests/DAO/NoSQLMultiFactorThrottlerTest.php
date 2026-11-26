<?php

namespace Test\Lucinda\Project\DAO;

use Lucinda\UnitTest\Result;
use Lucinda\UnitTest\Validator;
use Lucinda\Framework\Persistence;
use Lucinda\Project\DAO;
use Lucinda\Framework\Persistence\NoSqlExecutor;
use Test\Lucinda\Project\Support\RedisFixture;

/** Verifies NoSQLMultiFactorThrottler application behavior. */
final class NoSQLMultiFactorThrottlerTest
{
    /**
     * Enforces thresholds, expiry, canonical addresses and scope isolation
     *
     * @return Result[] Observed behavior assertions
     */
    private function testFailureWindows(): array
    {
        $results = [];
        $redis = new RedisFixture();
        $driver = $redis->getDriver();
        $executor = new NoSqlExecutor($driver, "framework-test:unit:");
        $dao = new DAO\NoSQLMultiFactorThrottler($executor, 2, 60);
        $results[] = (new Validator\Booleans(
            $dao->isThrottled('17', '::1') === false
        ))->assertTrue(
            'Absent failure window allows login'
        );
        $dao->penalize('17', '::1');
        $results[] = (new Validator\Booleans(
            $dao->isThrottled('17', '::1') === false
        ))->assertTrue(
            'One failure remains below threshold'
        );
        $dao->penalize('17', '::1');
        $results[] = (new Validator\Booleans(
            $dao->isThrottled('17', '0:0:0:0:0:0:0:1') === true
        ))->assertTrue(
            'Canonical IPv6 shares a failure window'
        );
        $results[] = (new Validator\Booleans(
            $dao->isThrottled('18', '::1') === false
        ))->assertTrue(
            'Different account has an independent window'
        );
        $results[] = (new Validator\Booleans(
            $dao->isThrottled('17', '127.0.0.1') === false
        ))->assertTrue(
            'Different client has an independent window'
        );
        $other = new DAO\NoSQLLoginThrottler($executor, 2, 60);
        $results[] = (new Validator\Booleans(
            $other->isThrottled('17', '::1') === false
        ))->assertTrue(
            'Form and MFA scopes remain separate'
        );
        $bucket = new Persistence\ThrottleBucket('mfa', '17', '::1');
        $key = $executor->getKeyspace()->getKey("throttle", $bucket->getKey());
        $record = $executor->read($key);
        $values = $record->getValue();
        $values["expires"] = time() - 1;
        $executor->replace($record, $values);
        $results[] = (new Validator\Booleans(
            $dao->isThrottled('17', '::1') === false
        ))->assertTrue(
            'Expired window does not block'
        );
        $dao->penalize('17', '::1');
        $results[] = (new Validator\Booleans(
            $dao->isThrottled('17', '::1') === false
        ))->assertTrue(
            'First failure after expiry starts a new window'
        );
        foreach ([[0, 60], [2, 0], [-1, 60]] as [$limit, $period]) {
            $rejected = false;
            try {
                new DAO\NoSQLMultiFactorThrottler($executor, $limit, $period);
            } catch (\InvalidArgumentException) {
                $rejected = true;
            }
            $results[] = (new Validator\Booleans(
                $rejected
            ))->assertTrue(
                'Invalid failure-window policy rejected'
            );
        }
        return $results;
    }
    /**
     * Verifies penalize through the complete application contract
     *
     * @return Result[] Assertions from the shared contract scenario
     */
    public function penalize(): array
    {
        return $this->testFailureWindows();
    }

    /**
     * Verifies isThrottled through the complete application contract
     *
     * @return Result[] Assertions from the shared contract scenario
     */
    public function isThrottled(): array
    {
        return $this->testFailureWindows();
    }
}
