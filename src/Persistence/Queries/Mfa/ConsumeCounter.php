<?php

namespace Lucinda\Project\Persistence\Queries\Mfa;

use Lucinda\Project\Persistence\Queries\Query;
use Lucinda\Query\Update;
use Lucinda\Query\Clause\Condition;
use Lucinda\Query\Operator\Logical as L;
use Lucinda\Query\Operator\Comparison as C;

/**
 * Atomically accepts a strictly newer eligible TOTP counter
 *
 * Owns the generated statement and bound values for this operation.
 *
 * @internal
 */
final class ConsumeCounter extends Query
{
    /**
     * Builds the statement for this operation
     *
     * @param int|string $user Local user identifier
     * @param int $counter Verified TOTP time-step counter
     * @param int $now Current Unix timestamp
     */
    public function __construct(
        int|string $user,
        int $counter,
        int $now
    ) {
        $this->setParameters($user, $counter, $now);
        $this->statement = new Update('user_mfa');
        $this->setChanges();
        $this->setWhere();
    }

    /**
     * Stores the runtime values bound by this operation
     *
     * @param int|string $user Local user identifier
     * @param int $counter Verified TOTP time-step counter
     * @param int $now Current Unix timestamp
     */
    private function setParameters(
        int|string $user,
        int $counter,
        int $now
    ): void {
        $this->parameters = [
            ':counter' => $counter,
            ':previous' => $counter,
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
        $this->statement->set(['last_counter' => ':counter']);
    }

    /**
     * Defines the conditions that constrain this operation
     *
     * @throws \Lucinda\Query\Exception If a clause definition is invalid
     */
    private function setWhere(): void
    {
        $fresh = new Condition([], L::_OR_);
        $fresh
            ->setIsNull('last_counter')
                ->set('last_counter', ':previous', C::LESSER);
        $enrolled = new Condition([], L::_OR_);
        $enrolled->setIsNull('secret', false);
        $pending = new Condition();
        $pending->setIsNull(
            'setup_secret',
            false
        )->set('setup_expires', ':now', C::GREATER);
        $enrolled->setGroup($pending);
        $this->statement->where(['user_id' => ':user'])
            ->setGroup($fresh)->setGroup($enrolled);
    }
}
