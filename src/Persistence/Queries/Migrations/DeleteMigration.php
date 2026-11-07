<?php

namespace Lucinda\Project\Persistence\Queries\Migrations;

use Lucinda\Project\Persistence\Queries\Query;
use Lucinda\Query\Delete;

/**
 * Removes the recorded status of one migration
 *
 * Owns the generated statement and bound values for this operation.
 *
 * @internal
 */
final class DeleteMigration extends Query
{
    /**
     * Builds the statement for this operation
     *
     * @param string $class Fully qualified migration class name
     */
    public function __construct(
        string $class
    ) {
        $this->setParameters($class);
        $this->statement = new Delete('migrations');
        $this->setWhere();
    }

    /**
     * Stores the runtime values bound by this operation
     *
     * @param string $class Fully qualified migration class name
     */
    private function setParameters(
        string $class
    ): void {
        $this->parameters = [':class' => $class];
    }

    /**
     * Defines the conditions that constrain this operation
     *
     * @throws \Lucinda\Query\Exception If a clause definition is invalid
     */
    private function setWhere(): void
    {
        $this->statement->where(['class_name' => ':class']);
    }
}
