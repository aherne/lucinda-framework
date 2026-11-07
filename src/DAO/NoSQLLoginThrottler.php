<?php

namespace Lucinda\Project\DAO;

use Lucinda\Project\Persistence\RedisExecutor;
use Lucinda\Project\Persistence\Redis\ThrottleStore;
use Lucinda\WebSecurity\DAO\Throttler\FormLogin as ThrottlerContract;

/**
 * Applies a fixed failure-window policy to form authentication attempts
 *
 * The dedicated store owns counter operations for this scope. Each rejected
 * attempt is recorded atomically by subject and canonical client address.
 */
final class NoSQLLoginThrottler implements ThrottlerContract
{
    /** Failure-window store retained for this authentication scope */
    private ThrottleStore $store;

    /**
     * Captures storage and the failure-window policy
     *
     * @param RedisExecutor|null $executor Shared persistence executor
     * @param int $limit Failures required to block one active window
     * @param int $period Fixed window lifetime in seconds
     * @throws \InvalidArgumentException If the policy is invalid
     */
    public function __construct(
        ?RedisExecutor $executor = null,
        int $limit = 5,
        int $period = 900
    ) {
        if ($executor === null) {
            $executor = new RedisExecutor();
        }
        $this->store = new ThrottleStore($executor, 'form', $limit, $period);
    }

    /**
     * Records a rejected authentication attempt in its failure window
     *
     * @param string $userName Submitted username or local account identifier
     * @param string $ipAddress Client address supplied by the request
     * @throws \Throwable If the failure cannot be persisted atomically
     */
    public function penalize(string $userName, string $ipAddress): void
    {
        $this->store->penalize((string) $userName, $ipAddress);
    }

    /**
     * Checks whether a live failure window currently blocks this attempt
     *
     * @param string $userName Submitted username or local account identifier
     * @param string $ipAddress Client address supplied by the request
     * @return bool True when the configured threshold has been reached
     * @throws \Throwable If the failure window cannot be read
     */
    public function isThrottled(string $userName, string $ipAddress): bool
    {
        return $this->store->blocked((string) $userName, $ipAddress);
    }
}
