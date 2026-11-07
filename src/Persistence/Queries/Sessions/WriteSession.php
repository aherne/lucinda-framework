<?php

namespace Lucinda\Project\Persistence\Queries\Sessions;

use Lucinda\Project\Persistence\Queries\Query;
use Lucinda\Query\Vendor\MySQL\Insert;

/**
 * Creates or replaces a session payload and expiration
 *
 * Owns the generated statement and bound values for this operation.
 *
 * @internal
 */
final class WriteSession extends Query
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
        $this->statement = new Insert('sessions');
        $this->setColumns();
        $this->setValues();
        $this->setDuplicateKeyUpdate();
    }

    /**
     * Stores the runtime values bound by this operation
     *
     * @param string $id Local record identifier
     * @param string $payload Serialized session bytes
     * @param int $expires Absolute expiration in Unix seconds
     */
    private function setParameters(
        string $id,
        string $payload,
        int $expires
    ): void {
        $this->parameters = [
            ':id' => $id,
            ':payload' => $payload,
            ':expires' => $expires,
            ':new_payload' => $payload,
            ':new_expires' => $expires,
        ];
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
     * Defines the atomic update applied to an existing record
     *
     * @throws \Lucinda\Query\Exception If a clause definition is invalid
     */
    private function setDuplicateKeyUpdate(): void
    {
        $this->statement->onDuplicateKeyUpdate([
            'payload' => ':new_payload',
            'expires' => ':new_expires',
        ]);
    }
}
