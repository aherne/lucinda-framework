<?php

namespace Lucinda\Project\Persistence\Queries\Throttles;

use Lucinda\Project\Persistence\Queries\Query;
use Lucinda\Query\Select;
use Lucinda\Query\Operator\Comparison as C;

/**
 * Selects a live window whose failure threshold was reached
 *
 * Owns the generated statement and bound values for this operation.
 *
 * @internal
 */
final class FindBlockedWindow extends Query
{
    /**
     * Builds the statement for this operation
     *
     * @param string $key Hashed authentication bucket identifier
     * @param int $now Current Unix timestamp
     * @param int $limit Failure threshold for the window
     */
    public function __construct(
        string $key,
        int $now,
        int $limit
    ) {
        $this->setParameters($key, $now, $limit);
        $this->statement = new Select('authentication_throttles');
        $this->setFields();
        $this->setWhere();
    }

    /**
     * Stores the runtime values bound by this operation
     *
     * @param string $key Hashed authentication bucket identifier
     * @param int $now Current Unix timestamp
     * @param int $limit Failure threshold for the window
     */
    private function setParameters(
        string $key,
        int $now,
        int $limit
    ): void {
        $this->parameters = [
            ':key' => $key,
            ':now' => $now,
            ':limit' => $limit,
        ];
    }

    /**
     * Defines selected columns and expression aliases
     *
     * @throws \Lucinda\Query\Exception If a clause definition is invalid
     */
    private function setFields(): void
    {
        $fields = $this->statement->fields();
        $fields->add('1', 'value');
    }

    /**
     * Defines the conditions that constrain this operation
     *
     * @throws \Lucinda\Query\Exception If a clause definition is invalid
     */
    private function setWhere(): void
    {
        $this->statement->where(['bucket' => ':key'])
            ->set(
                'expires',
                ':now',
                C::GREATER
            )->set('attempts', ':limit', C::GREATER_EQUALS);
    }
}
