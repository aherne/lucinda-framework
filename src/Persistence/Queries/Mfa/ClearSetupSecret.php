<?php

namespace Lucinda\Project\Persistence\Queries\Mfa;

use Lucinda\Project\Persistence\Queries\Query;
use Lucinda\Query\Update;

/**
 * Clears temporary state after enrollment while retaining replay data
 *
 * Owns the generated statement and bound values for this operation.
 *
 * @internal
 */
final class ClearSetupSecret extends Query
{
    /**
     * Builds the statement for this operation
     *
     * @param int|string $user Local user identifier
     */
    public function __construct(
        int|string $user
    ) {
        $this->setParameters($user);
        $this->statement = new Update('user_mfa');
        $this->setChanges();
        $this->setWhere();
    }

    /**
     * Stores the runtime values bound by this operation
     *
     * @param int|string $user Local user identifier
     */
    private function setParameters(
        int|string $user
    ): void {
        $this->parameters = [':user' => $user];
    }

    /**
     * Defines the values changed by this update
     *
     * @throws \Lucinda\Query\Exception If a clause definition is invalid
     */
    private function setChanges(): void
    {
        $this->statement->set([
            'setup_secret' => 'NULL',
            'setup_expires' => 'NULL',
        ]);
    }

    /**
     * Defines the conditions that constrain this operation
     *
     * @throws \Lucinda\Query\Exception If a clause definition is invalid
     */
    private function setWhere(): void
    {
        $where = $this->statement->where(['user_id' => ':user']);
        $where->setIsNull('secret', false);
    }
}
