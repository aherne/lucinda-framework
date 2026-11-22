<?php

namespace Lucinda\Project\DAO;

use Lucinda\Project\Persistence\Builders\RoleBuilder;
use Lucinda\Project\Persistence\Queries\Permissions\FindUserRoles;
use Lucinda\WebSecurity\DAO\UserRoles as UserRolesContract;

/**
 * Resolves explicit database roles for an authenticated local account
 *
 * Guests receive the GUESTS policy role. Authenticated accounts receive only
 * their persisted roles, and disabled accounts receive no database roles.
 */
final class UserRoles extends SqlBasedDAO implements UserRolesContract
{
    /**
     * Gets outward role names while retaining persistence entities internally
     *
     * @param int|string|null $userID Local account identifier or null for a
     *     guest
     * @return list<string> Roles applicable to the current account or guest
     * @throws \Throwable If role lookup or projection validation fails
     */
    public function getRoles(int|string|null $userID): array
    {
        if ($userID === null) {
            return ['GUESTS'];
        }
        $selection = $this->sql->read(new FindUserRoles($userID));
        $names = [];
        $builder = new RoleBuilder();
        while (($row = $selection->toRow()) !== null) {
            $role = $builder->build($row);
            $names[] = $role->getName();
        }
        return $names;
    }
}
