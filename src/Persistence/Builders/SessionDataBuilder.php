<?php

namespace Lucinda\Project\Persistence\Builders;

use Lucinda\Framework\Persistence\Builders\Builder;
use Lucinda\Project\Persistence\Entities\SessionData;

/**
 * Builds a SessionData entity from its selected database projection
 *
 * The builder owns one row and performs explicit type validation when building.
 *
 * @internal
 */
final class SessionDataBuilder extends Builder
{
    /**
     * Converts the selected values into the typed persistence entity
     *
     * @return SessionData Internal record constructed from this projection
     * @throws \UnexpectedValueException If required columns are malformed
     */
    public function build(): SessionData
    {
        return new SessionData($this->row->getString('payload'));
    }
}
