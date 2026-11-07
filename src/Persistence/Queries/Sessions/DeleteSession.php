<?php

namespace Lucinda\Project\Persistence\Queries\Sessions;

use Lucinda\Project\Persistence\Queries\Query;
use Lucinda\Query\Delete;

/**
 * Deletes one session without requiring it to exist
 *
 * Owns the generated statement and bound values for this operation.
 *
 * @internal
 */
final class DeleteSession extends Query
{
    /**
     * Builds the statement for this operation
     *
     * @param string $id Local record identifier
     */
    public function __construct(
        string $id
    ) {
        $this->setParameters($id);
        $this->statement = new Delete('sessions');
        $this->setWhere();
    }

    /**
     * Stores the runtime values bound by this operation
     *
     * @param string $id Local record identifier
     */
    private function setParameters(
        string $id
    ): void {
        $this->parameters = [':id' => $id];
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
