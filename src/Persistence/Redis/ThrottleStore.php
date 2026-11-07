<?php

namespace Lucinda\Project\Persistence\Redis;

use Lucinda\Project\Persistence\Redis\Scripts\IncrementWindow;
use Lucinda\Project\Persistence\Redis\Scripts\ReadCounter;
use Lucinda\Project\Persistence\RedisExecutor;
use Lucinda\Project\Persistence\ThrottleBucket;

/**
 * Owns Redis failure counters for one authentication scope and policy
 *
 * Counter creation and expiration execute atomically. Reads never extend the
 * lifetime of a blocked window, and separate scopes never share a counter.
 *
 * @internal
 */
final class ThrottleStore
{
    /** Executor used by this scope's counter operations */
    private RedisExecutor $executor;

    /** Authentication scope included in each bucket */
    private string $scope;

    /** Failure threshold for one active window */
    private int $limit;

    /** Fixed failure-window lifetime in seconds */
    private int $period;

    /**
     * Captures the executor and policy for one authentication scope
     *
     * @param RedisExecutor $executor Connection and keyspace for this store
     * @param string $scope Authentication operation name
     * @param int $limit Failure threshold for one window
     * @param int $period Window lifetime in seconds
     * @throws \InvalidArgumentException If the policy contains invalid values
     */
    public function __construct(
        RedisExecutor $executor,
        string $scope,
        int $limit = 5,
        int $period = 900
    ) {
        if ($limit < 1 || $period < 1) {
            throw new \InvalidArgumentException('Invalid throttle policy');
        }
        $this->executor = $executor;
        $this->scope = $scope;
        $this->limit = $limit;
        $this->period = $period;
    }

    /**
     * Resolves the Redis key for one subject and client address
     *
     * @param string $subject Submitted identity for this scope
     * @param string $ip Client address supplied by the request
     * @return string Application-scoped Redis counter key
     */
    private function getKey(string $subject, string $ip): string
    {
        $bucket = new ThrottleBucket($this->scope, $subject, $ip);
        $keyspace = $this->executor->getKeyspace();
        return $keyspace->getKey('throttle', $bucket->getKey());
    }

    /**
     * Atomically records one failed authentication attempt
     *
     * @param string $subject Submitted identity for this scope
     * @param string $ip Client address supplied by the request
     * @throws \Throwable If Redis cannot persist the counter
     */
    public function penalize(string $subject, string $ip): void
    {
        $script = new IncrementWindow(
            $this->getKey($subject, $ip),
            $this->period
        );
        $this->executor->execute($script);
    }

    /**
     * Checks whether this scope's failure threshold has been reached
     *
     * @param string $subject Submitted identity for this scope
     * @param string $ip Client address supplied by the request
     * @return bool True when the current failure window blocks the attempt
     * @throws \Throwable If Redis cannot read the counter
     */
    public function blocked(string $subject, string $ip): bool
    {
        $script = new ReadCounter($this->getKey($subject, $ip));
        $result = $this->executor->execute($script);
        return $result->getInteger() >= $this->limit;
    }
}
