<?php

namespace Lucinda\Project\Persistence\Queries\Sessions;

use Lucinda\Project\Persistence\Queries\Query;
use Lucinda\Query\Select;
use Lucinda\Query\Operator\Comparison as C;

/**
 * Reads an unexpired session payload
 *
 * Owns the generated statement and bound values for this operation.
 *
 * @internal
 */
final class ReadSession extends Query
{
    /**
     * Builds the statement for this operation
     *
     * @param string $id Local record identifier
     * @param int $now Current Unix timestamp
     */
    public function __construct(
        string $id,
        int $now
    ) {
        $this->setParameters($id, $now);
        $this->statement = new Select('sessions');
        $this->setFields();
        $this->setWhere();
    }

    /**
     * Stores the runtime values bound by this operation
     *
     * @param string $id Local record identifier
     * @param int $now Current Unix timestamp
     */
    private function setParameters(
        string $id,
        int $now
    ): void {
        $this->parameters = [':id' => $id, ':now' => $now];
    }

    /**
     * Defines selected columns and expression aliases
     *
     * @throws \Lucinda\Query\Exception If a clause definition is invalid
     */
    private function setFields(): void
    {
        $this->statement->fields(['payload']);
    }

    /**
     * Defines the conditions that constrain this operation
     *
     * @throws \Lucinda\Query\Exception If a clause definition is invalid
     */
    private function setWhere(): void
    {
        $where = $this->statement->where(['id' => ':id']);
        $where->set('expires', ':now', C::GREATER);
    }
}
