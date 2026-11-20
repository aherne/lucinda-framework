<?php

namespace Lucinda\Project\Persistence\Builders;

use Lucinda\Framework\Persistence\Builders\Builder;
use Lucinda\Project\Persistence\Entities\Role;

/**
 * Builds a Role entity from its selected database projection
 *
 * The builder owns one row and performs explicit type validation when building.
 *
 * @internal
 */
final class RoleBuilder extends Builder
{
    /**
     * Converts the selected values into the typed persistence entity
     *
     * @return Role Internal record constructed from this projection
     * @throws \UnexpectedValueException If required columns are malformed
     */
    public function build(): Role
    {
        return new Role($this->row->getString('name'));
    }
}
