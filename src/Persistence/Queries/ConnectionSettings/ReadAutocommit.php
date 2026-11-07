<?php

namespace Lucinda\Project\Persistence\Queries\ConnectionSettings;

use Lucinda\Project\Persistence\Queries\Query;
use Lucinda\Query\Select;

/**
 * Reads the connection autocommit setting
 *
 * Owns the generated statement and bound values for this operation.
 *
 * @internal
 */
final class ReadAutocommit extends Query
{
    /**
     * Builds the statement for this operation
     *
     * No runtime parameters are needed.
     */
    public function __construct()
    {
        $this->statement = new Select('DUAL');
        $this->setFields();
    }

    /**
     * Defines selected columns and expression aliases
     *
     * @throws \Lucinda\Query\Exception If a clause definition is invalid
     */
    private function setFields(): void
    {
        $fields = $this->statement->fields();
        $fields->add('@@autocommit', 'value');
    }
}
