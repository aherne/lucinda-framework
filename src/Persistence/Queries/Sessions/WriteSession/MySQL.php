<?php

namespace Lucinda\Project\Persistence\Queries\Sessions\WriteSession;

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
        return new VendorInsert('sessions');
    }

    /**
     * Applies the atomic conflict clause without a separate existence check
     *
     * @param array<string, string> $updates Bound update expressions
     * @throws \Lucinda\Query\Exception If a clause definition is invalid
     */
    protected function setConflictUpdate(array $updates): void
    {
        $this->statement->onDuplicateKeyUpdate($updates);
    }
}
