<?php

namespace Lucinda\Project\Persistence\Queries\Mfa;

use Lucinda\Project\Persistence\Queries\Query;
use Lucinda\Query\Vendor\MySQL\Insert;

/**
 * Creates the enrollment row without replacing existing state
 *
 * Owns the generated statement and bound values for this operation.
 *
 * @internal
 */
final class EnsureEnrollment extends Query
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
        $this->statement = new Insert('user_mfa');
        $this->setColumns();
        $this->setValues();
        $this->setDuplicateKeyUpdate();
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
     * Defines the columns written by this insertion
     *
     * @throws \Lucinda\Query\Exception If a clause definition is invalid
     */
    private function setColumns(): void
    {
        $this->statement->columns(['user_id']);
    }

    /**
     * Defines the bound value expressions for this insertion
     *
     * @throws \Lucinda\Query\Exception If a clause definition is invalid
     */
    private function setValues(): void
    {
        $this->statement->values([':user']);
    }

    /**
     * Defines the atomic update applied to an existing record
     *
     * @throws \Lucinda\Query\Exception If a clause definition is invalid
     */
    private function setDuplicateKeyUpdate(): void
    {
        $this->statement->onDuplicateKeyUpdate(['user_id' => 'user_id']);
    }
}
