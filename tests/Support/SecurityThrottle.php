<?php

namespace Test\Lucinda\Project\Support;

use Lucinda\WebSecurity\DAO\Throttler\FormLogin;

/** Owns local failed-attempt counts for an isolated security workflow. */
final class SecurityThrottle implements FormLogin
{
    /** @var array<string, int> Failure counts keyed by subject and address */
    private array $attempts = [];

    /**
     * Records one failed attempt within this fixture instance
     *
     * @param string $userName Submitted username
     * @param string $ipAddress Client address
     * @return void
     */
    public function penalize(string $userName, string $ipAddress): void
    {
        $key = $userName . '#' . $ipAddress;
        $this->attempts[$key] = ($this->attempts[$key] ?? 0) + 1;
    }

    /**
     * Applies the fixture's three-failure limit
     *
     * @param string $userName Submitted username
     * @param string $ipAddress Client address
     * @return bool Whether the fixture threshold has been reached
     */
    public function isThrottled(string $userName, string $ipAddress): bool
    {
        return ($this->attempts[$userName . '#' . $ipAddress] ?? 0) >= 3;
    }
}
