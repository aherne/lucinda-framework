<?php

namespace Lucinda\Project\Persistence\Queries\Migrations\FindMigrationTable;

use Lucinda\Framework\Persistence\Queries\Query;
use Lucinda\Framework\Persistence\Queries\Parameters;
use Lucinda\SQL\DataType;
use Lucinda\Query\Select;

/**
 * Checks whether the current schema contains migration storage
 *
 * Owns the selected marker and migration-table name across all dialects.
 *
 * @internal
 */
abstract class AbstractQuery extends Query
{
    /**
     * Builds the statement for this operation
     *
     * No runtime parameters are needed.
     */
    public function __construct()
    {
        $this->setParameters();
        $this->statement = new Select($this->getTable());
        $this->setFields();
        $this->setWhere();
    }

    /**
     * Stores the runtime values bound by this operation
     */
    protected function setParameters(): void
    {
        $this->parameters = new Parameters();
        $this->parameters->set(':name', 'migrations', DataType::STRING);
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
     * Identifies this vendor's table metadata source
     *
     * @return string Catalog table used for schema discovery
     */
    abstract protected function getTable(): string;

    /**
     * Restricts metadata to the migration table in the selected schema
     *
     * @throws \Lucinda\Query\Exception If a condition definition is invalid
     */
    abstract protected function setWhere(): void;
}
