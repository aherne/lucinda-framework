<?php

namespace Lucinda\Project\Persistence\Queries\Permissions;

use Lucinda\Project\Persistence\Queries\Query;
use Lucinda\Query\Select;

/**
 * Resolves a validated route to its persisted page policy
 *
 * Owns the generated statement and bound values for this operation.
 *
 * @internal
 */
final class FindPageByUrl extends Query
{
    /**
     * Builds the statement for this operation
     *
     * @param string $url Validated route identifier
     */
    public function __construct(
        string $url
    ) {
        $this->setParameters($url);
        $this->statement = new Select('resources');
        $this->setFields();
        $this->setWhere();
    }

    /**
     * Stores the runtime values bound by this operation
     *
     * @param string $url Validated route identifier
     */
    private function setParameters(
        string $url
    ): void {
        $this->parameters = [':url' => $url];
    }

    /**
     * Defines selected columns and expression aliases
     *
     * @throws \Lucinda\Query\Exception If a clause definition is invalid
     */
    private function setFields(): void
    {
        $this->statement->fields(['id', 'is_public']);
    }

    /**
     * Defines the conditions that constrain this operation
     *
     * @throws \Lucinda\Query\Exception If a clause definition is invalid
     */
    private function setWhere(): void
    {
        $this->statement->where(['url' => ':url']);
    }
}
