<?php

namespace Lucinda\Project\DAO;

use Lucinda\Framework\Persistence\NoSqlExecutor;
use Lucinda\Framework\Persistence\NoSqlThrottle;
use Lucinda\WebSecurity\DAO\Throttler\FormLogin as ThrottlerContract;

/**
 * Applies a fixed failure-window policy to form authentication attempts
 *
 * The dedicated store owns counter operations for this scope. Each rejected
 * attempt is recorded atomically by subject and canonical client address.
 */
final class NoSQLLoginThrottler extends NoSqlBasedDAO implements
    ThrottlerContract
{
    /**
     * Failure-window store retained for this authentication scope
     */
    private NoSqlThrottle $throttle;

    /**
     * Captures storage and the failure-window policy
     *
     * @param NoSqlExecutor $executor Shared persistence executor
     * @param int $limit Failures required to block one active window
     * @param int $period Fixed window lifetime in seconds
     * @throws \InvalidArgumentException If the policy is invalid
     */
    public function __construct(
        NoSqlExecutor $executor,
        int $limit = 5,
        int $period = 900
    ) {
        parent::__construct($executor);
        $this->throttle = new NoSqlThrottle($executor, 'form', $limit, $period);
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
        $this->throttle->penalize((string) $userName, $ipAddress);
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
        return $this->throttle->blocked((string) $userName, $ipAddress);
    }
}
