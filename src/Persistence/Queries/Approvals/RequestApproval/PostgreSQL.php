<?php

namespace Lucinda\Project\Persistence\Queries\Approvals\RequestApproval;

use Lucinda\Query\Insert;
use Lucinda\Query\Vendor\PostgreSQL\Insert as VendorInsert;

/**
 * Supplies PostgreSQL insertion and conflict syntax for this operation
 *
 * @internal
 */
final class PostgreSQL extends AbstractQuery
{
    /**
     * Creates the insertion builder for this vendor
     *
     * @return Insert Builder targeting the operation's persistence table
     */
    protected function createInsert(): Insert
    {
        return new VendorInsert('oauth2_approvals');
    }

    /**
     * Applies the atomic conflict clause without a separate existence check
     *
     * @throws \Lucinda\Query\Exception If a clause definition is invalid
     */
    protected function setConflictUpdate(): void
    {
        $this->statement->onConflictDoNothing(['vendor_name', 'remote_id']);
    }
}
