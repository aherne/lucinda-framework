<?php

namespace Lucinda\Project\Persistence\Queries\Mfa;

use Lucinda\Project\Persistence\Queries\Query;
use Lucinda\Query\Select;

/**
 * Reads account policy and optional TOTP enrollment
 *
 * Owns the generated statement and bound values for this operation.
 *
 * @internal
 */
final class FindEnrollment extends Query
{
    /**
     * Builds the statement for this operation
     *
     * @param int|string $user Local user identifier
     */
    public function __construct(
        int|string $user
    ) {
        $this->setParameters($user);
        $this->statement = new Select('users', 'u');
        $this->setFields();
        $this->setJoins();
        $this->setWhere();
    }

    /**
     * Stores the runtime values bound by this operation
     *
     * @param int|string $user Local user identifier
     */
    private function setParameters(
        int|string $user
    ): void {
        $this->parameters = [':user' => $user];
    }

    /**
     * Defines selected columns and expression aliases
     *
     * @throws \Lucinda\Query\Exception If a clause definition is invalid
     */
    private function setFields(): void
    {
        $fields = $this->statement->fields([
            'u.mfa_required',
            'm.secret',
            'm.setup_secret',
            'm.setup_expires',
            'm.last_counter',
        ]);
        $label = "COALESCE(NULLIF(u.email, ''), CONVERT(u.id, CHAR))";
        $fields->add($label, 'account_name');
    }

    /**
     * Defines the relationships needed by this operation
     *
     * @throws \Lucinda\Query\Exception If a clause definition is invalid
     */
    private function setJoins(): void
    {
        $join = $this->statement->joinLeft('user_mfa', 'm');
        $join->on(['u.id' => 'm.user_id']);
    }

    /**
     * Defines the conditions that constrain this operation
     *
     * @throws \Lucinda\Query\Exception If a clause definition is invalid
     */
    private function setWhere(): void
    {
        $this->statement->where(['u.id' => ':user', 'u.is_active' => '1']);
    }
}
