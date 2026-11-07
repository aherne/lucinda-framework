<?php

namespace Lucinda\Project\Persistence\Queries\Accounts;

use Lucinda\Project\Persistence\Queries\Query;
use Lucinda\Query\Insert;

/**
 * Links a provider identity to one local account
 *
 * Owns the generated statement and bound values for this operation.
 *
 * @internal
 */
final class LinkIdentity extends Query
{
    /**
     * Builds the statement for this operation
     *
     * @param int $id Local record identifier
     * @param string $vendor Configured OAuth2 provider name
     * @param string $remote Provider-assigned identity identifier
     */
    public function __construct(
        int $id,
        string $vendor,
        string $remote
    ) {
        $this->setParameters($id, $vendor, $remote);
        $this->statement = new Insert('oauth2_identities');
        $this->setColumns();
        $this->setValues();
    }

    /**
     * Stores the runtime values bound by this operation
     *
     * @param int $id Local record identifier
     * @param string $vendor Configured OAuth2 provider name
     * @param string $remote Provider-assigned identity identifier
     */
    private function setParameters(
        int $id,
        string $vendor,
        string $remote
    ): void {
        $this->parameters = [
            ':id' => $id,
            ':vendor' => $vendor,
            ':remote' => $remote,
        ];
    }

    /**
     * Defines the columns written by this insertion
     *
     * @throws \Lucinda\Query\Exception If a clause definition is invalid
     */
    private function setColumns(): void
    {
        $this->statement->columns(['user_id', 'vendor_name', 'remote_id']);
    }

    /**
     * Defines the bound value expressions for this insertion
     *
     * @throws \Lucinda\Query\Exception If a clause definition is invalid
     */
    private function setValues(): void
    {
        $this->statement->values([':id', ':vendor', ':remote']);
    }
}
