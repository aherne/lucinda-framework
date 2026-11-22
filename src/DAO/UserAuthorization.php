<?php

namespace Lucinda\Project\DAO;

use Lucinda\SQL\DataType;
use Lucinda\Project\Persistence\Queries\Permissions\FindPermission;
use Lucinda\WebSecurity\DAO\UserAuthorization as AuthorizationContract;

/**
 * Authorizes resource access using direct account grants
 *
 * Permission checks include the requested HTTP method. Missing grants deny
 * access, while an explicitly persisted wildcard permits every method.
 */
final class UserAuthorization extends SqlBasedDAO implements
    AuthorizationContract
{
    /**
     * Checks whether an active account has the requested resource permission
     *
     * @param int|string $userID Authenticated local account identifier
     * @param int $pageID Persisted resource identifier
     * @param string $httpRequestMethod Requested HTTP method
     * @return bool True when a matching direct or inherited grant exists
     * @throws \Throwable If the permission query fails
     */
    public function isAllowed(
        int|string $userID,
        int $pageID,
        string $httpRequestMethod
    ): bool {
        $query = new FindPermission(
            $userID,
            $pageID,
            $httpRequestMethod,
            false
        );
        $selection = $this->sql->read($query);
        return $selection->toValue(DataType::INTEGER) !== null;
    }
}
