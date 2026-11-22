<?php

namespace Lucinda\Project\Persistence\Queries\Mfa\EnsureEnrollment;

use Lucinda\Query\Insert;
use Lucinda\Query\Vendor\MySQL\Insert as VendorInsert;

/**
 * Supplies MySQL insertion and conflict syntax for this operation
 *
 * @internal
 */
final class MySQL extends AbstractQuery
{
    /**
     * Creates the insertion builder for this vendor
     *
     * @return Insert Builder targeting the operation's persistence table
     */
    protected function createInsert(): Insert
    {
        return new VendorInsert('user_mfa');
    }

    /**
     * Applies the atomic conflict clause without a separate existence check
     *
     * @throws \Lucinda\Query\Exception If a clause definition is invalid
     */
    protected function setConflictUpdate(): void
    {
        $this->statement->onDuplicateKeyUpdate(['user_id' => 'user_id']);
    }
}
