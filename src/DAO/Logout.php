<?php

namespace Lucinda\Project\DAO;

use Lucinda\WebSecurity\DAO\Logout as LogoutContract;

/**
 * Accepts logout for the application's locally persisted authentication state
 *
 * The security API clears authentication persistence after this operation.
 * This application stores no provider token requiring database revocation.
 */
final class Logout implements LogoutContract
{
    /**
     * Accepts the local logout operation for the current account
     *
     * @param int|string $userID Authenticated local account identifier
     * @return bool True so the security API can clear authentication state
     */
    public function logout(int|string $userID): bool
    {
        return true;
    }
}
