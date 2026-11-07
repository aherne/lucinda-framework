<?php

namespace Lucinda\Project\Persistence\Queries\Migrations;

use Lucinda\Project\Persistence\Queries\Query;
use Lucinda\Query\Vendor\MySQL\Insert;

/**
 * Records a migration status idempotently
 *
 * Owns the generated statement and bound values for this operation.
 *
 * @internal
 */
final class SaveMigration extends Query
{
    /**
     * Builds the statement for this operation
     *
     * @param string $class Fully qualified migration class name
     * @param int $status Backed migration status value
     */
    public function __construct(
        string $class,
        int $status
    ) {
        $this->setParameters($class, $status);
        $this->statement = new Insert('migrations');
        $this->setColumns();
        $this->setValues();
        $this->setDuplicateKeyUpdate();
    }

    /**
     * Stores the runtime values bound by this operation
     *
     * @param string $class Fully qualified migration class name
     * @param int $status Backed migration status value
     */
    private function setParameters(
        string $class,
        int $status
    ): void {
        $this->parameters = [
            ':class' => $class,
            ':status' => $status,
            ':now' => time(),
            ':updated_status' => $status,
            ':updated_now' => time(),
        ];
    }

    /**
     * Defines the columns written by this insertion
     *
     * @throws \Lucinda\Query\Exception If a clause definition is invalid
     */
    private function setColumns(): void
    {
        $this->statement->columns(['class_name', 'status_code', 'recorded_at']);
    }

    /**
     * Defines the bound value expressions for this insertion
     *
     * @throws \Lucinda\Query\Exception If a clause definition is invalid
     */
    private function setValues(): void
    {
        $this->statement->values([':class', ':status', ':now']);
    }

    /**
     * Defines the atomic update applied to an existing record
     *
     * @throws \Lucinda\Query\Exception If a clause definition is invalid
     */
    private function setDuplicateKeyUpdate(): void
    {
        $this->statement->onDuplicateKeyUpdate([
            'status_code' => ':updated_status',
            'recorded_at' => ':updated_now',
        ]);
    }
}
