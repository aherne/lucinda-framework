<?php

namespace Lucinda\Project\DAO;

use Lucinda\Project\Persistence\Queries\Accounts\CreateAccount;
use Lucinda\Project\Persistence\Queries\Accounts\LinkIdentity;
use Lucinda\SQL\StatementException;
use Lucinda\WebSecurity\DAO\OAuth2\AutomaticProvisioning;
use Lucinda\WebSecurity\DAO\OAuth2\UserInformation;

/**
 * Creates a local account and its OAuth2 identity link as one transaction
 *
 * Concurrent provisioning of the same identity is resolved through its unique
 * database key. Losing insertions roll back so they cannot orphan accounts.
 */
final class OAuth2AutomaticProvisioning extends OAuth2Login implements
    AutomaticProvisioning
{
    /**
     * Creates an eligible account or resolves a concurrently created identity
     *
     * @param UserInformation $userInformation Identity supplied by the provider
     * @param string $vendorName Configured OAuth2 provider name
     * @return int|string|null Accepted account identifier or null if rejected
     * @throws \Throwable If a non-recoverable database operation fails
     */
    public function create(
        UserInformation $userInformation,
        string $vendorName
    ): int|string|null {
        $remote = (string) $userInformation->getId();
        if ($vendorName === '' || $remote === '') {
            return null;
        }
        $id = $this->resolve($userInformation, $vendorName);
        if ($id !== null) {
            return $id;
        }
        $this->sql->begin();
        try {
            $accountQuery = new CreateAccount(
                $userInformation->getName(),
                $userInformation->getEmail()
            );
            $result = $this->sql->write($accountQuery);
            $id = $result->getInsertId();
            $this->sql->write(new LinkIdentity($id, $vendorName, $remote));
            $this->sql->commit();
            return $id;
        } catch (\Throwable $error) {
            $this->sql->rollback();
            if (
                $error instanceof StatementException
                && (int) $error->getCode() === 23000
            ) {
                return $this->resolve($userInformation, $vendorName);
            }
            throw $error;
        }
    }
}
