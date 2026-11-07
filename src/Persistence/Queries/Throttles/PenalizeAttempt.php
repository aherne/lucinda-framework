<?php

namespace Lucinda\Project\Persistence\Queries\Throttles;

use Lucinda\Project\Persistence\Queries\Query;
use Lucinda\Query\Vendor\MySQL\Insert;

/**
 * Atomically increments or resets a fixed failure window
 *
 * Owns the generated statement and bound values for this operation.
 *
 * @internal
 */
final class PenalizeAttempt extends Query
{
    /**
     * Builds the statement for this operation
     *
     * @param string $key Hashed authentication bucket identifier
     * @param int $now Current Unix timestamp
     * @param int $period Failure window lifetime in seconds
     */
    public function __construct(
        string $key,
        int $now,
        int $period
    ) {
        $this->setParameters($key, $now, $period);
        $this->statement = new Insert('authentication_throttles');
        $this->setColumns();
        $this->setValues();
        $this->setDuplicateKeyUpdate();
    }

    /**
     * Stores the runtime values bound by this operation
     *
     * @param string $key Hashed authentication bucket identifier
     * @param int $now Current Unix timestamp
     * @param int $period Failure window lifetime in seconds
     */
    private function setParameters(
        string $key,
        int $now,
        int $period
    ): void {
        $this->parameters = [
            ':key' => $key,
            ':expires' => $now + $period,
            ':now' => $now,
            ':reset_now' => $now,
            ':reset_expires' => $now + $period,
        ];
    }

    /**
     * Defines the columns written by this insertion
     *
     * @throws \Lucinda\Query\Exception If a clause definition is invalid
     */
    private function setColumns(): void
    {
        $this->statement->columns(['bucket', 'attempts', 'expires']);
    }

    /**
     * Defines the bound value expressions for this insertion
     *
     * @throws \Lucinda\Query\Exception If a clause definition is invalid
     */
    private function setValues(): void
    {
        $this->statement->values([':key', '1', ':expires']);
    }

    /**
     * Defines the atomic update applied to an existing record
     *
     * @throws \Lucinda\Query\Exception If a clause definition is invalid
     */
    private function setDuplicateKeyUpdate(): void
    {
        $this->statement->onDuplicateKeyUpdate([
            'attempts' => 'IF(expires <= :now, 1, LEAST(attempts + 1, 1000000))',
            'expires' => 'IF(expires <= :reset_now, :reset_expires, expires)',
        ]);
    }
}
