<?php

namespace Lucinda\Project\Persistence\Queries\Accounts;

use Lucinda\Project\Persistence\Queries\Query;
use Lucinda\Query\Insert;

/**
 * Inserts a local account without inferring identity from email
 *
 * Owns the generated statement and bound values for this operation.
 *
 * @internal
 */
final class CreateAccount extends Query
{
    /**
     * Builds the statement for this operation
     *
     * @param string $name Display name supplied for the account
     * @param string $email Optional profile email address
     */
    public function __construct(
        string $name,
        string $email
    ) {
        $this->setParameters($name, $email);
        $this->statement = new Insert('users');
        $this->setColumns();
        $this->setValues();
    }

    /**
     * Stores the runtime values bound by this operation
     *
     * @param string $name Display name supplied for the account
     * @param string $email Optional profile email address
     */
    private function setParameters(
        string $name,
        string $email
    ): void {
        if ($email === '') {
            $email = null;
        }
        $this->parameters = [
            ':name' => $name,
            ':email' => $email,
        ];
    }

    /**
     * Defines the columns written by this insertion
     *
     * @throws \Lucinda\Query\Exception If a clause definition is invalid
     */
    private function setColumns(): void
    {
        $this->statement->columns(['display_name', 'email']);
    }

    /**
     * Defines the bound value expressions for this insertion
     *
     * @throws \Lucinda\Query\Exception If a clause definition is invalid
     */
    private function setValues(): void
    {
        $this->statement->values([':name', ':email']);
    }
}
