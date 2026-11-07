<?php

namespace Lucinda\Project\Persistence\Queries\Permissions;

use Lucinda\Project\Persistence\Queries\Query;
use Lucinda\Query\Select;

/**
 * Reads public access policy for one resource
 *
 * Owns the generated statement and bound values for this operation.
 *
 * @internal
 */
final class FindPageById extends Query
{
    /**
     * Builds the statement for this operation
     *
     * @param int $id Local record identifier
     */
    public function __construct(
        int $id
    ) {
        $this->setParameters($id);
        $this->statement = new Select('resources');
        $this->setFields();
        $this->setWhere();
    }

    /**
     * Stores the runtime values bound by this operation
     *
     * @param int $id Local record identifier
     */
    private function setParameters(
        int $id
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
        $this->statement->fields(['id', 'is_public']);
    }

    /**
     * Defines the conditions that constrain this operation
     *
     * @throws \Lucinda\Query\Exception If a clause definition is invalid
     */
    private function setWhere(): void
    {
        $this->statement->where(['id' => ':id']);
    }
}
