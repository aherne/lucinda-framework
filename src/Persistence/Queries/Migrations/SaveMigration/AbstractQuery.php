<?php

namespace Lucinda\Project\Persistence\Queries\Migrations\SaveMigration;

use Lucinda\Framework\Persistence\Queries\Query;
use Lucinda\Framework\Persistence\Queries\Parameters;
use Lucinda\SQL\DataType;
use Lucinda\Query\Insert;

/**
 * Records a migration status idempotently
 *
 * Owns the values and statement clauses common to all supported dialects.
 *
 * @internal
 */
abstract class AbstractQuery extends Query
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
        $this->statement = $this->createInsert();
        $this->setColumns();
        $this->setValues();
        $this->setConflictUpdate($this->getChanges());
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
        $this->parameters = new Parameters();
        $this->parameters->set(':class', $class, DataType::STRING);
        $this->parameters->set(':status', $status, DataType::INTEGER);
        $this->parameters->set(':now', time(), DataType::INTEGER);
        $this->parameters->set(':updated_status', $status, DataType::INTEGER);
        $this->parameters->set(':updated_now', time(), DataType::INTEGER);
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
     * Defines changes applied atomically to an existing record
     *
     * @return array<string, string> Columns and bound update expressions
     */
    private function getChanges(): array
    {
        return [
            'status_code' => ':updated_status',
            'recorded_at' => ':updated_now',
        ];
    }

    /**
     * Creates the insertion builder for the selected vendor
     *
     * @return Insert Builder configured for this operation's table
     */
    abstract protected function createInsert(): Insert;

    /**
     * Defines this vendor's atomic conflict behavior
     *
     * @param array<string, string> $updates Bound update expressions
     * @throws \Lucinda\Query\Exception If a clause definition is invalid
     */
    abstract protected function setConflictUpdate(array $updates): void;
}
