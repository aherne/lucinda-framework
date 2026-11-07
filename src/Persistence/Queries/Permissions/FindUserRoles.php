<?php

namespace Lucinda\Project\Persistence\Queries\Permissions;

use Lucinda\Project\Persistence\Queries\Query;
use Lucinda\Query\Select;

/**
 * Selects database roles of an active account
 *
 * Owns the generated statement and bound values for this operation.
 *
 * @internal
 */
final class FindUserRoles extends Query
{
    /**
     * Builds the statement for this operation
     *
     * @param int|string $id Local record identifier
     */
    public function __construct(
        int|string $id
    ) {
        $this->setParameters($id);
        $this->statement = new Select('users_roles', 'ur');
        $this->setFields();
        $this->setJoins();
        $this->setWhere();
    }

    /**
     * Stores the runtime values bound by this operation
     *
     * @param int|string $id Local record identifier
     */
    private function setParameters(
        int|string $id
    ): void {
        $this->parameters = [':id' => $id];
    }

    /**
     * Defines selected columns and expression aliases
     *
     * @throws \Lucinda\Query\Exception If a clause definition is invalid
     */
    private function setFields(): void
    {
        $this->statement->fields(['r.name']);
    }

    /**
     * Defines the relationships needed by this operation
     *
     * @throws \Lucinda\Query\Exception If a clause definition is invalid
     */
    private function setJoins(): void
    {
        $this->statement->joinInner('roles', 'r')->on(['ur.role_id' => 'r.id']);
        $this->statement->joinInner('users', 'u')->on(['ur.user_id' => 'u.id']);
    }

    /**
     * Defines the conditions that constrain this operation
     *
     * @throws \Lucinda\Query\Exception If a clause definition is invalid
     */
    private function setWhere(): void
    {
        $this->statement->where(['ur.user_id' => ':id', 'u.is_active' => '1']);
    }
}
