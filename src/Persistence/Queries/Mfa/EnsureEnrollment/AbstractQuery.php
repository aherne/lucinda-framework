<?php

namespace Lucinda\Project\Persistence\Queries\Mfa\EnsureEnrollment;

use Lucinda\Framework\Persistence\Queries\Query;
use Lucinda\Framework\Persistence\Queries\Parameters;
use Lucinda\SQL\DataType;
use Lucinda\Query\Insert;

/**
 * Creates the enrollment row without replacing existing state
 *
 * Owns the values and statement clauses common to all supported dialects.
 *
 * @internal
 */
abstract class AbstractQuery extends Query
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
        $this->statement = $this->createInsert();
        $this->setColumns();
        $this->setValues();
        $this->setConflictUpdate();
    }

    /**
     * Stores the runtime values bound by this operation
     *
     * @param int|string $user Local user identifier
     */
    private function setParameters(
        int|string $user
    ): void {
        $this->parameters = new Parameters();
        $this->parameters->set(':user', $user, DataType::INTEGER);
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
     * Creates the insertion builder for the selected vendor
     *
     * @return Insert Builder configured for this operation's table
     */
    abstract protected function createInsert(): Insert;

    /**
     * Defines this vendor's atomic conflict behavior
     *
     * @throws \Lucinda\Query\Exception If a clause definition is invalid
     */
    abstract protected function setConflictUpdate(): void;
}
