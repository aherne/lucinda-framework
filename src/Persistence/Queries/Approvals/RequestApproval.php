<?php

namespace Lucinda\Project\Persistence\Queries\Approvals;

use Lucinda\Project\Persistence\Queries\Query;
use Lucinda\Query\Vendor\MySQL\Insert;

/**
 * Records an approval request without replacing an existing decision
 *
 * Owns the generated statement and bound values for this operation.
 *
 * @internal
 */
final class RequestApproval extends Query
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
        $this->statement = new Insert('oauth2_approvals');
        $this->setColumns();
        $this->setValues();
        $this->setDuplicateKeyUpdate();
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
        $this->parameters = [
            ':vendor' => $vendor,
            ':remote' => $remote,
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
     * Defines the atomic update applied to an existing record
     *
     * @throws \Lucinda\Query\Exception If a clause definition is invalid
     */
    private function setDuplicateKeyUpdate(): void
    {
        $this->statement->onDuplicateKeyUpdate(['remote_id' => 'remote_id']);
    }
}
