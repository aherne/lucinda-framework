<?php

namespace Lucinda\Project\Persistence\Queries\States;

use Lucinda\Project\Persistence\Queries\Query;
use Lucinda\Query\Delete;
use Lucinda\Query\Operator\Comparison as Comparison;

/**
 * Deletes expired attempts optionally limited to one browser proof
 *
 * Optional state parameters restrict callback cleanup without changing the
 * expiration-only maintenance operation.
 *
 * @internal
 */
final class DeleteExpiredStates extends Query
{
    /**
     * Captures the cleanup scope and assembles its expiration condition
     *
     * @param int $now Current Unix timestamp
     * @param string|null $state Optional authorization state to clean up
     * @param string|null $proof Hash of the browser proof for that state
     */
    public function __construct(
        int $now,
        ?string $state = null,
        ?string $proof = null
    ) {
        $this->setParameters($now, $state, $proof);
        $this->statement = new Delete('oauth2_states');
        $this->setWhere();
    }

    /**
     * Stores expiration and optional browser-specific cleanup values
     *
     * @param int $now Current Unix timestamp
     * @param string|null $state Optional authorization state to clean up
     * @param string|null $proof Hash of the browser proof for that state
     */
    private function setParameters(
        int $now,
        ?string $state,
        ?string $proof
    ): void {
        $this->parameters = [':now' => $now];
        if ($state !== null) {
            $this->parameters[':state'] = $state;
            $this->parameters[':proof'] = $proof;
        }
    }

    /**
     * Restricts deletion to expired attempts within the captured scope
     *
     * @throws \Lucinda\Query\Exception If a condition definition is invalid
     */
    private function setWhere(): void
    {
        $where = $this->statement->where();
        $where->set('valid_until', ':now', Comparison::LESSER_EQUALS);
        if (isset($this->parameters[':state'])) {
            $where->set('state', ':state');
            $where->set('browser_proof_hash', ':proof');
        }
    }
}
