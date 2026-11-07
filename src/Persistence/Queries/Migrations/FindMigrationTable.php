<?php

namespace Lucinda\Project\Persistence\Queries\Migrations;

use Lucinda\Project\Persistence\Queries\Query;
use Lucinda\Query\Select;

/**
 * Checks whether the current schema contains migration storage
 *
 * Owns the generated statement and bound values for this operation.
 *
 * @internal
 */
final class FindMigrationTable extends Query
{
    /**
     * Builds the statement for this operation
     *
     * No runtime parameters are needed.
     */
    public function __construct()
    {
        $this->setParameters();
        $this->statement = new Select('information_schema.tables');
        $this->setFields();
        $this->setWhere();
    }

    /**
     * Stores the runtime values bound by this operation
     *
     */
    private function setParameters(): void
    {
        $this->parameters = [':name' => 'migrations'];
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
        $this->statement->where([
            'table_schema' => 'DATABASE()',
            'table_name' => ':name',
        ]);
    }
}
