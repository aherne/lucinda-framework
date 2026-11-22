<?php

namespace Test\Lucinda\Project\Persistence;

use Lucinda\Framework\Persistence\Locking\Lock;
use Lucinda\Framework\Persistence\Locking\NoSqlLock;
use Lucinda\Framework\Persistence\NoSqlExecutor;
use Lucinda\Framework\Persistence\Queries\ValidationException;

/**
 * Exercises lease ownership and protected writes against real NoSQL adapters
 */
final class NoSqlLockChecks
{
    /** Executor confined to this integration run's namespace */
    private NoSqlExecutor $executor;

    /** @var list<string> Record identities for targeted cleanup */
    private array $keys = [];

    /** Number of completed assertions */
    private int $count = 0;

    /**
     * Captures the isolated backend used by competing lease owners
     *
     * @param NoSqlExecutor $executor Test connection and unique namespace
     */
    public function __construct(NoSqlExecutor $executor)
    {
        $this->executor = $executor;
    }

    /**
     * Requires an invariant and records the completed assertion
     *
     * @param bool $condition Observed result
     * @param string $message Description of a failed invariant
     * @throws \RuntimeException If the invariant is false
     */
    private function check(bool $condition, string $message): void
    {
        $this->count++;
        if (!$condition) {
            throw new \RuntimeException($message);
        }
    }

    /**
     * Registers an identity used by a lease test
     *
     * @param string $purpose Storage domain
     * @param string $identity Record identity
     * @return string Fully namespaced test key
     */
    private function key(string $purpose, string $identity): string
    {
        $key = $this->executor->getKeyspace()->getKey($purpose, $identity);
        $this->keys[] = $key;
        return $key;
    }

    /**
     * Gets only records owned by this isolated suite
     *
     * @return list<string> Keys eligible for targeted cleanup
     */
    public function getKeys(): array
    {
        return $this->keys;
    }

    /**
     * Runs ownership, contention, renewal and stale-writer checks
     *
     * @return int Number of completed assertions
     */
    public function run(): int
    {
        $this->checkPolicy();
        $this->checkAcquisition();
        $this->checkRenewal();
        $this->checkSuccessor();
        $this->checkExpiration();
        $this->checkMalformedRecord();
        return $this->count;
    }

    /**
     * Requires valid policy and an acquired identity before reading
     */
    private function checkPolicy(): void
    {
        foreach ([['', 60, 0], ['lock', 0, 0], ['lock', 60, -1]] as $policy) {
            $rejected = false;
            try {
                new NoSqlLock($this->executor, $policy[0], $policy[1], $policy[2]);
            } catch (\InvalidArgumentException) {
                $rejected = true;
            }
            $this->check($rejected, 'Invalid NoSQL lease policy accepted');
        }
        $lock = new NoSqlLock($this->executor);
        $rejected = false;
        try {
            $lock->read();
        } catch (\LogicException) {
            $rejected = true;
        }
        $this->check($rejected, 'An unacquired lease permitted reading');
        $lock->release();
    }

    /**
     * Requires exclusive, idempotent acquisition and independent domains
     */
    private function checkAcquisition(): void
    {
        $key = $this->key('lock', 'shared');
        $otherKey = $this->key('lock', 'other');
        $this->key('independent-lock', 'shared');
        $first = new NoSqlLock($this->executor, 'lock', 60, 0);
        $second = new NoSqlLock($this->executor, 'lock', 60, 0);
        $independent = new NoSqlLock(
            $this->executor,
            'independent-lock',
            60,
            0
        );
        $this->check($first instanceof Lock, 'NoSQL lease implements Lock');
        $first->acquire('shared');
        $snapshot = $first->read();
        $first->acquire('shared');
        $this->check(
            $first->read()->getVersion() === $snapshot->getVersion(),
            'Repeated acquisition changed ownership or renewed the lease'
        );
        $blocked = false;
        try {
            $second->acquire('shared');
        } catch (\RuntimeException $error) {
            $blocked = str_contains($error->getMessage(), 'timed out');
        }
        $this->check($blocked, 'A contender acquired an existing lease');
        $independent->acquire('shared');
        $this->check(true, 'Different lock domains remain independent');
        $independent->release();
        $first->acquire('other');
        $this->check(!$this->executor->read($key)->exists(), 'Old lease kept');
        $second->acquire('shared');
        $first->release();
        $second->release();
        $second->release();
        $this->check(
            !$this->executor->read($key)->exists()
                && !$this->executor->read($otherKey)->exists(),
            'Standalone release did not conditionally remove its record'
        );
    }

    /**
     * Requires renewal to retain payload, token and longer record retention
     */
    private function checkRenewal(): void
    {
        $key = $this->key('lock', 'renew');
        $lock = new NoSqlLock($this->executor, 'lock', 60, 0);
        $lock->acquire('renew');
        $this->check(
            $lock->replace($lock->read(), ['value' => "binary\0\xff"], 180),
            'Protected record initialization failed'
        );
        $snapshot = $this->executor->read($key);
        $values = $snapshot->getValue();
        $values['lease_expires'] = time() + 20;
        $this->executor->replace($snapshot, $values, 180);
        $before = $lock->read();
        $lock->renew();
        $after = $lock->read();
        $this->check(
            $after->getValue()['lease_expires']
                > $before->getValue()['lease_expires'],
            'Renewal failed to extend the ownership deadline'
        );
        $this->check(
            $after->getValue()['token'] === $before->getValue()['token']
                && $after->getValue()['value'] === "binary\0\xff"
                && $after->getExpiresAt() >= $before->getExpiresAt(),
            'Renewal changed token, payload or shortened retention'
        );
        $this->check(
            !$lock->replace($before, ['value' => 'obsolete'], 180),
            'A pre-renewal observation overwrote the renewed record'
        );
        $lock->release();
        $record = $this->executor->read($key)->getValue();
        $this->check(
            $record['token'] === null && $record['lease_expires'] === 0
                && $record['value'] === "binary\0\xff",
            'Release discarded data sharing the lease record'
        );
    }

    /**
     * Requires a successor to reject stale writes, renewal and release
     */
    private function checkSuccessor(): void
    {
        $key = $this->key('lock', 'successor');
        $first = new NoSqlLock($this->executor, 'lock', 60, 0);
        $second = new NoSqlLock($this->executor, 'lock', 60, 0);
        $first->acquire('successor');
        $stale = $first->read();
        $values = $stale->getValue();
        $values['lease_expires'] = time() - 1;
        $this->executor->replace($stale, $values, 180);
        $rejected = false;
        try {
            $first->acquire('successor');
        } catch (\RuntimeException) {
            $rejected = true;
        }
        $this->check($rejected, 'An expired lifecycle silently reacquired');
        $second->acquire('successor');
        $second->replace($second->read(), ['value' => 'winner'], 180);
        $this->check(
            !$first->replace($stale, ['value' => 'stale'], 180),
            'A stale writer overwrote its successor'
        );
        $rejected = false;
        try {
            $first->renew();
        } catch (\RuntimeException) {
            $rejected = true;
        }
        $this->check($rejected, 'A stale owner renewed its successor lease');
        $first->release();
        $this->check(
            $second->read()->getValue()['value'] === 'winner',
            'A stale release changed or deleted its successor'
        );
        $second->release();
        $this->check(
            $this->executor->read($key)->getValue()['value'] === 'winner',
            'The successor lost its protected data on release'
        );
    }

    /**
     * Requires real backend expiration to recover an abandoned lease
     */
    private function checkExpiration(): void
    {
        $this->key('lock', 'expired');
        $first = new NoSqlLock($this->executor, 'lock', 1, 0);
        $second = new NoSqlLock($this->executor, 'lock', 60, 0);
        $first->acquire('expired');
        usleep(1100000);
        $second->acquire('expired');
        $first->release();
        $this->check($second->read()->exists(), 'Expired owner deleted lock');
        $second->release();
    }

    /**
     * Requires corrupt ownership metadata to fail without replacement
     */
    private function checkMalformedRecord(): void
    {
        $key = $this->key('lock', 'malformed');
        $this->executor->replace($this->executor->read($key), [
            'token' => ['invalid'],
            'lease_expires' => time() + 60,
        ], 60);
        $before = $this->executor->read($key);
        $lock = new NoSqlLock($this->executor, 'lock', 60, 0);
        $rejected = false;
        try {
            $lock->acquire('malformed');
        } catch (ValidationException $error) {
            $rejected = $error->getExpectedValueType() === 'string'
                && $error->getActualValueType() === 'array';
        }
        $this->check(
            $rejected && $before->getVersion()
                === $this->executor->read($key)->getVersion(),
            'A malformed lock was treated as available storage'
        );
    }
}
