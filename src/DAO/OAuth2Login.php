<?php

namespace Lucinda\Project\DAO;

use Lucinda\Project\Persistence\Builders\IdentityBuilder;
use Lucinda\Project\Persistence\Queries\Accounts\FindIdentity;
use Lucinda\WebSecurity\DAO\OAuth2\Login;
use Lucinda\WebSecurity\DAO\OAuth2\UserInformation;

/**
 * Resolves an OAuth2 identity to an eligible existing local account
 *
 * Lookup is read-only and uses provider name plus remote identifier. Email is
 * profile data and never causes implicit linking to another local account.
 */
class OAuth2Login extends SqlBasedDAO implements Login
{
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
        $selection = $this->sql->read($query);
        $row = $selection->toRow();
        if ($row === null) {
            return null;
        }
        $builder = new IdentityBuilder();
        $identity = $builder->build($row);
        return $identity->getUserID();
    }
}
