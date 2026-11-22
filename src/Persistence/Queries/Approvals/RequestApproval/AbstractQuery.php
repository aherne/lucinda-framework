<?php

namespace Lucinda\Project\Persistence\Queries\Approvals\RequestApproval;

use Lucinda\Framework\Persistence\Queries\Query;
use Lucinda\Framework\Persistence\Queries\Parameters;
use Lucinda\SQL\DataType;
use Lucinda\Query\Insert;

/**
 * Records an approval request without replacing an existing decision
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
     * @param string $vendor Configured OAuth2 provider name
     * @param string $remote Provider-assigned identity identifier
     * @param string $name Display name supplied for the account
     * @param string $email Optional profile email address
     */
    public function __construct(
        string $vendor,
        string $remote,
        string $name,
        string $email
    ) {
        $this->setParameters($vendor, $remote, $name, $email);
        $this->statement = $this->createInsert();
        $this->setColumns();
        $this->setValues();
        $this->setConflictUpdate();
    }

    /**
     * Stores the runtime values bound by this operation
     *
     * @param string $vendor Configured OAuth2 provider name
     * @param string $remote Provider-assigned identity identifier
     * @param string $name Display name supplied for the account
     * @param string $email Optional profile email address
     */
    private function setParameters(
        string $vendor,
        string $remote,
        string $name,
        string $email
    ): void {
        if ($email === '') {
            $email = null;
        }
        $this->parameters = new Parameters();
        $this->parameters->set(':vendor', $vendor, DataType::STRING);
        $this->parameters->set(':remote', $remote, DataType::STRING);
        $this->parameters->set(':name', $name, DataType::STRING);
        $this->parameters->set(
            ':email',
            $email,
            $email === null ? DataType::NULLABLE : DataType::STRING
        );
    }

    /**
     * Defines the columns written by this insertion
     *
     * @throws \Lucinda\Query\Exception If a clause definition is invalid
     */
    private function setColumns(): void
    {
        $this->statement->columns([
            'vendor_name',
            'remote_id',
            'display_name',
            'email',
        ]);
    }

    /**
     * Defines the bound value expressions for this insertion
     *
     * @throws \Lucinda\Query\Exception If a clause definition is invalid
     */
    private function setValues(): void
    {
        $this->statement->values([':vendor', ':remote', ':name', ':email']);
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
