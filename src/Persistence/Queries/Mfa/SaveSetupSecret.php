<?php

namespace Lucinda\Project\Persistence\Queries\Mfa;

use Lucinda\Project\Persistence\Queries\Query;
use Lucinda\Query\Update;
use Lucinda\Query\Clause\Condition;
use Lucinda\Query\Operator\Logical as L;
use Lucinda\Query\Operator\Comparison as C;

/**
 * Stores a fresh setup secret only when enrollment permits it
 *
 * Owns the generated statement and bound values for this operation.
 *
 * @internal
 */
final class SaveSetupSecret extends Query
{
    /**
     * Builds the statement for this operation
     *
     * @param int|string $user Local user identifier
     * @param string $cipher Authenticated encrypted TOTP setup secret
     * @param int $now Current Unix timestamp
     * @param int $expires Absolute expiration in Unix seconds
     */
    public function __construct(
        int|string $user,
        string $cipher,
        int $now,
        int $expires
    ) {
        $this->setParameters($user, $cipher, $now, $expires);
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
     * @param int $expires Absolute expiration in Unix seconds
     */
    private function setParameters(
        int|string $user,
        string $cipher,
        int $now,
        int $expires
    ): void {
        $this->parameters = [
            ':cipher' => $cipher,
            ':expires' => $expires,
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
        $this->statement->set([
            'setup_secret' => ':cipher',
            'setup_expires' => ':expires',
            'last_counter' => 'NULL',
        ]);
    }

    /**
     * Defines the conditions that constrain this operation
     *
     * @throws \Lucinda\Query\Exception If a clause definition is invalid
     */
    private function setWhere(): void
    {
        $or = new Condition([], L::_OR_);
        $or->setIsNull('setup_secret')
            ->set('setup_expires', ':now', C::LESSER_EQUALS);
        $where = $this->statement->where(['user_id' => ':user']);
        $where->setIsNull('secret');
        $where->setGroup($or);
    }
}
