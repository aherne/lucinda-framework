<?php

namespace Lucinda\Project\Persistence\Builders;

use Lucinda\Framework\Persistence\Builders\Builder;
use Lucinda\Framework\Persistence\Builders\Row;
use Lucinda\Project\Persistence\Entities\Approval;

/**
 * Builds a Approval entity from its selected database projection
 *
 * Each call validates its supplied row and builds an independent entity.
 *
 * @internal
 */
final class ApprovalBuilder implements Builder
{
    /**
     * Converts the selected values into the typed persistence entity
     *
     * @param Row $row Selected projection with typed column access
     * @return Approval Internal record constructed from this projection
     * @throws \Lucinda\Framework\Persistence\Queries\ValidationException
     *     If required columns are missing or malformed
     */
    public function build(Row $row): Approval
    {
        return new Approval($row->getString('status'));
    }
}
