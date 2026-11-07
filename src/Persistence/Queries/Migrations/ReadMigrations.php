<?php

namespace Lucinda\Project\Persistence\Queries\Migrations;

use Lucinda\Project\Persistence\Queries\Query;
use Lucinda\Query\Select;

/**
 * Reads each recorded migration status
 *
 * Owns the generated statement and bound values for this operation.
 *
 * @internal
 */
final class ReadMigrations extends Query
{
    /**
     * Builds the statement for this operation
     *
     * No runtime parameters are needed.
     */
    public function __construct()
    {
        $this->statement = new Select('migrations');
        $this->setFields();
    }

    /**
     * Defines selected columns and expression aliases
     *
     * @throws \Lucinda\Query\Exception If a clause definition is invalid
     */
    private function setFields(): void
    {
        $this->statement->fields(['class_name', 'status_code']);
    }
}
