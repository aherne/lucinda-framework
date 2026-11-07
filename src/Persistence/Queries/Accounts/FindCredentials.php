<?php

namespace Lucinda\Project\Persistence\Queries\Accounts;

use Lucinda\Project\Persistence\Queries\Query;
use Lucinda\Query\Select;

/**
 * Selects credentials of an active local account
 *
 * Owns the generated statement and bound values for this operation.
 *
 * @internal
 */
final class FindCredentials extends Query
{
    /**
     * Builds the statement for this operation
     *
     * @param string $username Case-sensitive submitted login name
     */
    public function __construct(
        string $username
    ) {
        $this->setParameters($username);
        $this->statement = new Select('user_credentials', 'c');
        $this->setFields();
        $this->setJoins();
        $this->setWhere();
    }

    /**
     * Stores the runtime values bound by this operation
     *
     * @param string $username Case-sensitive submitted login name
     */
    private function setParameters(
        string $username
    ): void {
        $this->parameters = [':username' => $username];
    }

    /**
     * Defines selected columns and expression aliases
     *
     * @throws \Lucinda\Query\Exception If a clause definition is invalid
     */
    private function setFields(): void
    {
        $this->statement->fields(['c.user_id', 'c.password_hash']);
    }

    /**
     * Defines the relationships needed by this operation
     *
     * @throws \Lucinda\Query\Exception If a clause definition is invalid
     */
    private function setJoins(): void
    {
        $this->statement->joinInner('users', 'u')->on(['c.user_id' => 'u.id']);
    }

    /**
     * Defines the conditions that constrain this operation
     *
     * @throws \Lucinda\Query\Exception If a clause definition is invalid
     */
    private function setWhere(): void
    {
        $this->statement->where([
            'c.username' => ':username',
            'u.is_active' => '1',
        ]);
    }
}
