<?php

namespace Lucinda\Project\Persistence\Queries\Sessions\WriteSession;

use Lucinda\Framework\Persistence\Queries\Query;
use Lucinda\Framework\Persistence\Queries\Parameters;
use Lucinda\SQL\DataType;
use Lucinda\Query\Insert;

/**
 * Creates or replaces a session payload and expiration
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
     * @param string $id Local record identifier
     * @param string $payload Serialized session bytes
     * @param int $expires Absolute expiration in Unix seconds
     */
    public function __construct(
        string $id,
        string $payload,
        int $expires
    ) {
        $this->setParameters($id, $payload, $expires);
        $this->statement = $this->createInsert();
        $this->setColumns();
        $this->setValues();
        $this->setConflictUpdate($this->getChanges());
    }

    /**
     * Stores the runtime values bound by this operation
     *
     * @param string $id Local record identifier
     * @param string $payload Serialized session bytes
     * @param int $expires Absolute expiration in Unix seconds
     */
    protected function setParameters(
        string $id,
        string $payload,
        int $expires
    ): void {
        $this->parameters = new Parameters();
        $this->parameters->set(':id', $id, DataType::STRING);
        $this->parameters->set(':payload', $payload, DataType::STRING);
        $this->parameters->set(':expires', $expires, DataType::INTEGER);
        $this->parameters->set(':new_payload', $payload, DataType::STRING);
        $this->parameters->set(':new_expires', $expires, DataType::INTEGER);
    }

    /**
     * Defines the columns written by this insertion
     *
     * @throws \Lucinda\Query\Exception If a clause definition is invalid
     */
    private function setColumns(): void
    {
        $this->statement->columns(['id', 'payload', 'expires']);
    }

    /**
     * Defines the bound value expressions for this insertion
     *
     * @throws \Lucinda\Query\Exception If a clause definition is invalid
     */
    private function setValues(): void
    {
        $this->statement->values([':id', ':payload', ':expires']);
    }

    /**
     * Defines changes applied atomically to an existing record
     *
     * @return array<string, string> Columns and bound update expressions
     */
    private function getChanges(): array
    {
        return [
            'payload' => ':new_payload',
            'expires' => ':new_expires',
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
