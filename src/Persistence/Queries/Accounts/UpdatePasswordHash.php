<?php

namespace Lucinda\Project\Persistence\Queries\Accounts;

use Lucinda\Project\Persistence\Queries\Query;
use Lucinda\Query\Update;

/**
 * Replaces a password hash only if its previous value still matches
 *
 * Owns the generated statement and bound values for this operation.
 *
 * @internal
 */
final class UpdatePasswordHash extends Query
{
    /**
     * Builds the statement for this operation
     *
     * @param int $id Local record identifier
     * @param string $oldHash Hash expected before the update
     * @param string $newHash Replacement password hash
     */
    public function __construct(
        int $id,
        string $oldHash,
        string $newHash
    ) {
        $this->setParameters($id, $oldHash, $newHash);
        $this->statement = new Update('user_credentials');
        $this->setChanges();
        $this->setWhere();
    }

    /**
     * Stores the runtime values bound by this operation
     *
     * @param int $id Local record identifier
     * @param string $oldHash Hash expected before the update
     * @param string $newHash Replacement password hash
     */
    private function setParameters(
        int $id,
        string $oldHash,
        string $newHash
    ): void {
        $this->parameters = [
            ':new' => $newHash,
            ':id' => $id,
            ':old' => $oldHash,
        ];
    }

    /**
     * Defines the values changed by this update
     *
     * @throws \Lucinda\Query\Exception If a clause definition is invalid
     */
    private function setChanges(): void
    {
        $this->statement->set(['password_hash' => ':new']);
    }

    /**
     * Defines the conditions that constrain this operation
     *
     * @throws \Lucinda\Query\Exception If a clause definition is invalid
     */
    private function setWhere(): void
    {
        $this->statement->where([
            'user_id' => ':id',
            'password_hash' => ':old',
        ]);
    }
}
