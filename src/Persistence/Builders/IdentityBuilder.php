<?php

namespace Lucinda\Project\Persistence\Builders;

use Lucinda\Framework\Persistence\Builders\Builder;
use Lucinda\Project\Persistence\Entities\Identity;

/**
 * Builds a Identity entity from its selected database projection
 *
 * The builder owns one row and performs explicit type validation when building.
 *
 * @internal
 */
final class IdentityBuilder extends Builder
{
    /**
     * Converts the selected values into the typed persistence entity
     *
     * @return Identity Internal record constructed from this projection
     * @throws \UnexpectedValueException If required columns are malformed
     */
    public function build(): Identity
    {
        return new Identity($this->row->getInteger('user_id'));
    }
}
