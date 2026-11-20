<?php

namespace Lucinda\Project\Persistence\Builders;

use Lucinda\Framework\Persistence\Builders\Builder;
use Lucinda\Project\Persistence\Entities\Approval;

/**
 * Builds a Approval entity from its selected database projection
 *
 * The builder owns one row and performs explicit type validation when building.
 *
 * @internal
 */
final class ApprovalBuilder extends Builder
{
    /**
     * Converts the selected values into the typed persistence entity
     *
     * @return Approval Internal record constructed from this projection
     * @throws \UnexpectedValueException If required columns are malformed
     */
    public function build(): Approval
    {
        return new Approval($this->row->getString('status'));
    }
}
