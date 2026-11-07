<?php

namespace Lucinda\Project\DAO;

use Lucinda\Project\Persistence\Builders\IdentityBuilder;
use Lucinda\Project\Persistence\Queries\Accounts\FindIdentity;
use Lucinda\Project\Persistence\SqlExecutor;
use Lucinda\WebSecurity\DAO\OAuth2\Login;
use Lucinda\WebSecurity\DAO\OAuth2\UserInformation;

/**
 * Resolves an OAuth2 identity to an eligible existing local account
 *
 * Lookup is read-only and uses provider name plus remote identifier. Email is
 * profile data and never causes implicit linking to another local account.
 */
class OAuth2Login implements Login
{
    /**
     * Executor shared by identity lookup and optional provisioning
     * operations
     */
    protected SqlExecutor $sql;

    /**
     * Captures an injected executor or a lazily configured SQL connection
     *
     * @param SqlExecutor|null $sql Executor sharing the caller's connection
     */
    public function __construct(?SqlExecutor $sql = null)
    {
        $this->sql = $sql ?? new SqlExecutor();
    }

    /**
     * Looks up an active account without changing account or identity records
     *
     * @param UserInformation $userInformation Identity supplied by the provider
     * @param string $vendorName Configured OAuth2 provider name
     * @return int|string|null Eligible local identifier or null when unresolved
     * @throws \Throwable If identity lookup or projection validation fails
     */
    public function resolve(
        UserInformation $userInformation,
        string $vendorName
    ): int|string|null {
        $query = new FindIdentity(
            $vendorName,
            (string) $userInformation->getId()
        );
        $selection = $this->sql->select($query);
        $row = $selection->getFirstRow();
        if ($row === null) {
            return null;
        }
        $builder = new IdentityBuilder($row);
        $identity = $builder->build();
        return $identity->getUserID();
    }
}
