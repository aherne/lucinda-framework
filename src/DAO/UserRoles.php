<?php

namespace Lucinda\Project\DAO;

use Lucinda\Project\Persistence\Builders\RoleBuilder;
use Lucinda\Project\Persistence\Queries\Permissions\FindUserRoles;
use Lucinda\Project\Persistence\SqlExecutor;
use Lucinda\WebSecurity\DAO\UserRoles as UserRolesContract;

/**
 * Resolves explicit database roles for an authenticated local account
 *
 * Guests receive the GUESTS policy role. Authenticated accounts receive only
 * their persisted roles, and disabled accounts receive no database roles.
 */
final class UserRoles implements UserRolesContract
{
    /** Executor used for account role projections */
    private SqlExecutor $sql;

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
        $selection = $this->sql->select(new FindUserRoles($userID));
        $names = [];
        foreach ($selection->getRows() as $row) {
            $builder = new RoleBuilder($row);
            $role = $builder->build();
            $names[] = $role->getName();
        }
        return $names;
    }
}
