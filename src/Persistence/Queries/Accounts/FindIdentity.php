<?php

namespace Lucinda\Project\Persistence\Queries\Accounts;

use Lucinda\Project\Persistence\Queries\Query;
use Lucinda\Query\Select;

/**
 * Finds an active account linked to a provider identity
 *
 * Owns the generated statement and bound values for this operation.
 *
 * @internal
 */
final class FindIdentity extends Query
{
    /**
     * Builds the statement for this operation
     *
     * @param string $vendor Configured OAuth2 provider name
     * @param string $remote Provider-assigned identity identifier
     */
    public function __construct(
        string $vendor,
        string $remote
    ) {
        $this->setParameters($vendor, $remote);
        $this->statement = new Select('oauth2_identities', 'i');
        $this->setFields();
        $this->setJoins();
        $this->setWhere();
    }

    /**
     * Stores the runtime values bound by this operation
     *
     * @param string $vendor Configured OAuth2 provider name
     * @param string $remote Provider-assigned identity identifier
     */
    private function setParameters(
        string $vendor,
        string $remote
    ): void {
        $this->parameters = [':vendor' => $vendor, ':remote' => $remote];
    }

    /**
     * Defines selected columns and expression aliases
     *
     * @throws \Lucinda\Query\Exception If a clause definition is invalid
     */
    private function setFields(): void
    {
        $this->statement->fields(['i.user_id']);
    }

    /**
     * Defines the relationships needed by this operation
     *
     * @throws \Lucinda\Query\Exception If a clause definition is invalid
     */
    private function setJoins(): void
    {
        $this->statement->joinInner('users', 'u')->on(['i.user_id' => 'u.id']);
    }

    /**
     * Defines the conditions that constrain this operation
     *
     * @throws \Lucinda\Query\Exception If a clause definition is invalid
     */
    private function setWhere(): void
    {
        $this->statement->where([
            'i.vendor_name' => ':vendor',
            'i.remote_id' => ':remote',
            'u.is_active' => '1',
        ]);
    }
}
