<?php

namespace Test\Lucinda\Project\Persistence;

use Lucinda\Migration\Status;
use Lucinda\Project\DAO;
use Lucinda\Framework\NoSQL\RepresentationStore;
use Lucinda\Framework\NoSQL\SessionStore;
use Lucinda\Framework\Persistence\NoSqlExecutor;

/**
 * Verifies the same persistence guarantees against each real NoSQL adapter
 */
final class NoSqlChecks
{
    /** Executor isolated to this test run's namespace */
    private NoSqlExecutor $executor;

    /** Number of assertions completed by this instance */
    private int $count = 0;

    /** @var list<string> Known record keys for targeted test cleanup */
    private array $keys = [];

    /**
     * Captures the isolated executor
     *
     * @param NoSqlExecutor $executor Adapter under integration testing
     */
    public function __construct(NoSqlExecutor $executor)
    {
        $this->executor = $executor;
    }

    /**
     * Requires an invariant and counts its completed check
     *
     * @param bool $condition Observed assertion result
     * @param string $message Failure description
     * @throws \RuntimeException If the invariant fails
     */
    private function check(bool $condition, string $message): void
    {
        $this->count++;
        if (!$condition) {
            throw new \RuntimeException($message);
        }
    }

    /**
     * Records a key that this suite may create
     *
     * @param string $purpose Storage domain
     * @param string $identity Domain identity
     * @return string Namespaced record key
     */
    private function key(string $purpose, string $identity): string
    {
        $key = $this->executor->getKeyspace()->getKey($purpose, $identity);
        $this->keys[] = $key;
        return $key;
    }

    /**
     * Gets only this suite's record identities for safe cleanup
     *
     * @return list<string> Keys inside the unique test namespace
     */
    public function getKeys(): array
    {
        return $this->keys;
    }

    /**
     * Exercises compare-and-swap, expiration, leases and domain isolation
     *
     * @return int Completed assertion count
     */
    public function run(): int
    {
        $this->checkReplacement();
        $this->checkThrottle();
        $this->checkSessions();
        $this->checkMigrations();
        $this->checkRepresentations();
        return $this->count;
    }

    /**
     * Requires atomic initialization and rejection of stale observations
     */
    private function checkReplacement(): void
    {
        $key = $this->key('test', 'cas');
        $first = $this->executor->read($key);
        $second = $this->executor->read($key);
        $this->check(!$first->exists(), 'Record initially absent');
        $this->check(
            $this->executor->replace($first, ['value' => 1], 2592001),
            'Atomic initial creation'
        );
        $this->check(
            !$this->executor->replace($second, ['value' => 2]),
            'Competing initialization cannot overwrite the winner'
        );
        $first = $this->executor->read($key);
        $second = $this->executor->read($key);
        $this->check($first->getValue()['value'] === 1, 'Long retention');
        $this->check(
            $this->executor->replace($first, ['value' => 3]),
            'Current observation accepted'
        );
        $this->check(
            !$this->executor->replace($second, ['value' => 4]),
            'Stale observation rejected'
        );
    }

    /**
     * Requires canonical keys, scope separation and fixed expiration windows
     */
    private function checkThrottle(): void
    {
        $bucket = new \Lucinda\Framework\Persistence\ThrottleBucket(
            'form',
            'u',
            '::1'
        );
        $key = $this->key('throttle', $bucket->getKey());
        $throttle = new DAO\NoSQLLoginThrottler($this->executor, 2, 30);
        $this->check(!$throttle->isThrottled('u', '::1'), 'Absent counter');
        $throttle->penalize('u', '::1');
        $this->check(!$throttle->isThrottled('u', '::1'), 'First failure');
        $expires = $this->executor->read($key)->getValue()['expires'];
        $throttle->penalize('u', '::1');
        $this->check(
            $throttle->isThrottled('u', '0:0:0:0:0:0:0:1'),
            'Equivalent IPv6 representations share one counter'
        );
        $this->check(
            $this->executor->read($key)->getValue()['expires'] === $expires,
            'Repeated failures do not extend the window'
        );
        $mfa = new DAO\NoSQLMultiFactorThrottler($this->executor);
        $this->check(!$mfa->isThrottled('u', '::1'), 'MFA scope isolated');
        $snapshot = $this->executor->read($key);
        $this->executor->replace($snapshot, [
            'attempts' => 10,
            'expires' => time() - 1,
        ]);
        $this->check(!$throttle->isThrottled('u', '::1'), 'Expired window');
        $throttle->penalize('u', '::1');
        $this->check(
            $this->executor->read($key)->getValue()['attempts'] === 1,
            'Expired window atomically reset'
        );
    }

    /**
     * Requires binary payloads, retained locking and rejection of stale owners
     */
    private function checkSessions(): void
    {
        $key = $this->key('session', 's');
        $first = new SessionStore($this->executor, 60, 1);
        $second = new SessionStore($this->executor, 60, 1);
        $this->check($first->read('s') === '', 'Absent session');
        $first->write('s', "x\0\xffy", 60);
        $this->check($first->read('s') === "x\0\xffy", 'Binary session');
        $blocked = false;
        try {
            $second->read('s');
        } catch (\RuntimeException $error) {
            $blocked = str_contains($error->getMessage(), 'timed out');
        }
        $this->check($blocked, 'Session lease retained through close');
        $snapshot = $this->executor->read($key);
        $values = $snapshot->getValue();
        $values['lease_expires'] = time() + 20;
        $this->executor->replace($snapshot, $values, 120);
        $first->renew();
        $renewed = $this->executor->read($key)->getValue();
        $this->check(
            $renewed['lease_expires'] > $values['lease_expires']
                && $renewed['payload_expires'] === $values['payload_expires']
                && $renewed['payload'] === "x\0\xffy",
            'Session renewal preserves payload and its deadline'
        );
        $first->close();
        $this->check($second->read('s') === "x\0\xffy", 'Released lease');
        $second->destroy('s');
        $second->destroy('s');
        $this->check($second->read('s') === '', 'Idempotent destruction');
        $second->close();
        $first->read('s');
        $snapshot = $this->executor->read($key);
        $values = $snapshot->getValue();
        $values['lease_expires'] = time() - 1;
        $this->executor->replace($snapshot, $values);
        $rejected = false;
        try {
            $first->write('s', 'expired', 60);
        } catch (\RuntimeException) {
            $rejected = true;
        }
        $this->check($rejected, 'Expired session cannot silently reacquire');
        $second->write('s', 'winner', 60);
        $rejected = false;
        try {
            $first->write('s', 'stale', 60);
        } catch (\RuntimeException) {
            $rejected = true;
        }
        $this->check($rejected, 'Expired owner cannot overwrite a new owner');
        $first->close();
        $this->check($second->read('s') === 'winner', 'Stale release harmless');
        $snapshot = $this->executor->read($key);
        $values = $snapshot->getValue();
        $values['payload_expires'] = time() - 1;
        $this->executor->replace($snapshot, $values);
        $this->check($second->read('s') === '', 'Payload expiry enforced');
        $second->close();
    }

    /**
     * Requires idempotent initialization and every migration status
     */
    private function checkMigrations(): void
    {
        $this->key('migrations', 'records');
        $cache = new DAO\NoSQLMigrationCache($this->executor);
        $this->check(!$cache->exists(), 'Absent migration ledger');
        $cache->create();
        $cache->create();
        $this->check($cache->exists(), 'Idempotent ledger initialization');
        $cache->add('A', Status::PASSED);
        $cache->add('B', Status::FAILED);
        $cache->add('Pending', Status::PENDING);
        $cache->create();
        $statuses = $cache->read();
        $this->check(
            count($statuses) === 3 && $statuses['A'] === Status::PASSED
                && $statuses['B'] === Status::FAILED
                && $statuses['Pending'] === Status::PENDING,
            'Initialization preserves all status values'
        );
        $cache->add('A', Status::FAILED);
        $this->check($cache->read()['A'] === Status::FAILED, 'Status update');
        foreach (array_keys($cache->read()) as $name) {
            $cache->remove($name);
        }
        $cache->remove('A');
        $this->check($cache->read() === [], 'Idempotent status removal');
    }

    /**
     * Requires stable timestamps and renewal only after retained expiry
     */
    private function checkRepresentations(): void
    {
        $key = $this->key('representation', 'body');
        $store = new RepresentationStore($this->executor, 60);
        $time = $store->getTime('body');
        $this->check($store->getTime('body') === $time, 'Stable timestamp');
        $snapshot = $this->executor->read($key);
        $this->executor->replace($snapshot, [
            'time' => 1,
            'expires' => time() - 1,
        ]);
        $this->check($store->getTime('body') > 1, 'Expired timestamp renewed');
    }
}
