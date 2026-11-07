<?php

namespace Lucinda\Project\Persistence;

use Lucinda\Project\Persistence\Queries\Throttles\DeleteExpiredWindows;
use Lucinda\Project\Persistence\Queries\Throttles\FindBlockedWindow;
use Lucinda\Project\Persistence\Queries\Throttles\PenalizeAttempt;

/**
 * Owns the SQL failure-window policy for one authentication scope
 *
 * Writes use an atomic MySQL upsert. Reads inspect live windows without
 * changing their lifetime or mixing form and MFA attempts.
 *
 * @internal
 */
final class SqlThrottle
{
    /** Executor used for this scope's failure windows */
    private SqlExecutor $sql;

    /** Authentication scope included in every bucket identity */
    private string $scope;

    /** Number of failures that blocks an attempt */
    private int $limit;

    /** Fixed window lifetime in seconds */
    private int $period;

    /**
     * Captures the executor and failure policy for this authentication scope
     *
     * @param SqlExecutor $sql Executor sharing this operation's connection
     * @param string $scope Authentication operation name
     * @param int $limit Failure threshold for one window
     * @param int $period Window lifetime in seconds
     * @throws \InvalidArgumentException If the threshold or lifetime is invalid
     */
    public function __construct(
        SqlExecutor $sql,
        string $scope,
        int $limit = 5,
        int $period = 900
    ) {
        if ($limit < 1 || $period < 1) {
            throw new \InvalidArgumentException('Invalid throttle policy');
        }
        $this->sql = $sql;
        $this->scope = $scope;
        $this->limit = $limit;
        $this->period = $period;
    }

    /**
     * Atomically records a rejected attempt in its fixed failure window
     *
     * @param string $subject Submitted identity for this scope
     * @param string $ip Canonicalizable client address
     * @throws \Throwable If immediate persistence cannot be guaranteed
     */
    public function penalize(string $subject, string $ip): void
    {
        $this->sql->requireAutocommit();
        $bucket = new ThrottleBucket($this->scope, $subject, $ip);
        $query = new PenalizeAttempt(
            $bucket->getKey(),
            time(),
            $this->period
        );
        $this->sql->write($query);
    }

    /**
     * Checks whether a live window reached the configured failure threshold
     *
     * @param string $subject Submitted identity for this scope
     * @param string $ip Canonicalizable client address
     * @return bool True when this scope currently blocks the attempt
     * @throws \Throwable If the window cannot be read
     */
    public function blocked(string $subject, string $ip): bool
    {
        $bucket = new ThrottleBucket($this->scope, $subject, $ip);
        $query = new FindBlockedWindow(
            $bucket->getKey(),
            time(),
            $this->limit
        );
        $selection = $this->sql->select($query);
        return !$selection->isEmpty();
    }

    /**
     * Deletes abandoned windows after their expiration time
     *
     * @throws \Throwable If expired windows cannot be deleted
     */
    public function clearExpired(): void
    {
        $this->sql->write(new DeleteExpiredWindows(time()));
    }
}
