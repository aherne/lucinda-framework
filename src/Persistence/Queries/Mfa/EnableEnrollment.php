<?php

namespace Lucinda\Project\Persistence\Queries\Mfa;

use Lucinda\Project\Persistence\Queries\Query;
use Lucinda\Query\Update;
use Lucinda\Query\Operator\Comparison as C;

/**
 * Promotes a verified unexpired setup secret to enrolled state
 *
 * Owns the generated statement and bound values for this operation.
 *
 * @internal
 */
final class EnableEnrollment extends Query
{
    /**
     * Builds the statement for this operation
     *
     * @param int|string $user Local user identifier
     * @param string $cipher Authenticated encrypted TOTP setup secret
     * @param int $now Current Unix timestamp
     */
    public function __construct(
        int|string $user,
        string $cipher,
        int $now
    ) {
        $this->setParameters($user, $cipher, $now);
        $this->statement = new Update('user_mfa');
        $this->setChanges();
        $this->setWhere();
    }

    /**
     * Stores the runtime values bound by this operation
     *
     * @param int|string $user Local user identifier
     * @param string $cipher Authenticated encrypted TOTP setup secret
     * @param int $now Current Unix timestamp
     */
    private function setParameters(
        int|string $user,
        string $cipher,
        int $now
    ): void {
        $this->parameters = [
            ':cipher' => $cipher,
            ':expected' => $cipher,
            ':user' => $user,
            ':now' => $now,
        ];
    }

    /**
     * Defines the values changed by this update
     *
     * @throws \Lucinda\Query\Exception If a clause definition is invalid
     */
    private function setChanges(): void
    {
        $this->statement->set(['secret' => ':cipher']);
    }

    /**
     * Defines the conditions that constrain this operation
     *
     * @throws \Lucinda\Query\Exception If a clause definition is invalid
     */
    private function setWhere(): void
    {
        $where = $this->statement->where([
            'user_id' => ':user',
            'setup_secret' => ':expected',
        ]);
        $where->setIsNull('secret');
        $where->setIsNull('last_counter', false);
        $where->set('setup_expires', ':now', C::GREATER);
    }
}
