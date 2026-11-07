<?php

namespace Lucinda\Project\Persistence\Queries\Permissions;

use Lucinda\Project\Persistence\Queries\Query;
use Lucinda\Query\Select;

/**
 * Selects a method-specific direct or inherited permission
 *
 * Statement clauses are assembled separately from runtime parameter values.
 * The role strategy selects fixed table and column names defined by code.
 *
 * @internal
 */
final class FindPermission extends Query
{
    /** Whether permissions are inherited through persisted role membership */
    private bool $roles;

    /** Fixed user column selected by this operation's permission strategy */
    private string $userColumn = 'p.user_id';

    /**
     * Captures the strategy and assembles its independent query clauses
     *
     * @param int|string $user Local account identifier
     * @param int $page Persisted resource identifier
     * @param string $method Requested HTTP method
     * @param bool $roles Whether grants are inherited through database roles
     */
    public function __construct(
        int|string $user,
        int $page,
        string $method,
        bool $roles
    ) {
        $this->roles = $roles;
        $this->setParameters($user, $page, $method);
        $this->setStatement();
        $this->setFields();
        $this->setJoins();
        $this->setWhere();
        $this->setLimit();
    }

    /**
     * Stores runtime values without interpolating them into statement clauses
     *
     * @param int|string $user Local account identifier
     * @param int $page Persisted resource identifier
     * @param string $method Requested HTTP method
     */
    private function setParameters(
        int|string $user,
        int $page,
        string $method
    ): void {
        $this->parameters = [
            ':user' => $user,
            ':page' => $page,
            ':method' => strtoupper($method),
            ':any' => '*',
        ];
    }

    /**
     * Selects the fixed permission table and user column for this strategy
     */
    private function setStatement(): void
    {
        $table = 'users_resources';
        if ($this->roles) {
            $table = 'roles_resources';
            $this->userColumn = 'ur.user_id';
        }
        $this->statement = new Select($table, 'p');
    }

    /**
     * Selects an existence marker through the field alias API
     *
     * @throws \Lucinda\Query\Exception If the field definition is invalid
     */
    private function setFields(): void
    {
        $fields = $this->statement->fields();
        $fields->add('1', 'value');
    }

    /**
     * Defines role membership and active-account relationships
     *
     * @throws \Lucinda\Query\Exception If a join definition is invalid
     */
    private function setJoins(): void
    {
        if ($this->roles) {
            $join = $this->statement->joinInner('users_roles', 'ur');
            $join->on(['p.role_id' => 'ur.role_id']);
        }
        $join = $this->statement->joinInner('users', 'u');
        $join->on([$this->userColumn => 'u.id']);
    }

    /**
     * Requires the account, resource and allowed method to match
     *
     * @throws \Lucinda\Query\Exception If a condition definition is invalid
     */
    private function setWhere(): void
    {
        $where = $this->statement->where([
            $this->userColumn => ':user',
            'p.resource_id' => ':page',
            'u.is_active' => '1',
        ]);
        $where->setIn('p.http_method', [':method', ':any']);
    }

    /**
     * Stops after finding the first qualifying grant
     */
    private function setLimit(): void
    {
        $this->statement->limit(1);
    }
}
