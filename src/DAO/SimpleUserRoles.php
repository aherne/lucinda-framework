<?php

namespace Lucinda\Project\DAO;

use Lucinda\WebSecurity\DAO\UserRoles as UserRolesContract;

/**
 * Supplies the simple guest and member policy without database role storage
 *
 * Use this implementation when every authenticated account has the same role.
 */
final class SimpleUserRoles implements UserRolesContract
{
    /**
     * Classifies the authenticated account or unauthenticated request
     *
     * @param int|string|null $userID Local account identifier or null for a
     *     guest
     * @return list<string> GUESTS for a guest or MEMBERS for an account
     */
    public function getRoles(int|string|null $userID): array
    {
        if ($userID === null) {
            return ['GUESTS'];
        }
        return ['MEMBERS'];
    }
}
