<?php

namespace Lucinda\Project\Persistence\Queries\Sessions;

use Lucinda\Project\Persistence\Queries\Query;
use Lucinda\Query\Delete;
use Lucinda\Query\Operator\Comparison as C;

/**
 * Deletes session records whose lifetime has elapsed
 *
 * Owns the generated statement and bound values for this operation.
 *
 * @internal
 */
final class DeleteExpiredSessions extends Query
{
    /**
     * Builds the statement for this operation
     *
     * @param int $now Current Unix timestamp
     */
    public function __construct(
        int $now
    ) {
        $this->setParameters($now);
        $this->statement = new Delete('sessions');
        $this->setWhere();
    }

    /**
     * Stores the runtime values bound by this operation
     *
     * @param int $now Current Unix timestamp
     */
    private function setParameters(
        int $now
    ): void {
        $this->parameters = [':now' => $now];
    }

    /**
     * Defines the conditions that constrain this operation
     *
     * @throws \Lucinda\Query\Exception If a clause definition is invalid
     */
    private function setWhere(): void
    {
        $this->statement->where()->set('expires', ':now', C::LESSER_EQUALS);
    }
}
