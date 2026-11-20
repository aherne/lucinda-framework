<?php

namespace Lucinda\Project\Persistence\Builders;

use Lucinda\Framework\Persistence\Builders\Builder;
use Lucinda\Project\Persistence\Entities\Credentials;

/**
 * Builds a Credentials entity from its selected database projection
 *
 * The builder owns one row and performs explicit type validation when building.
 *
 * @internal
 */
final class CredentialsBuilder extends Builder
{
    /**
     * Converts the selected values into the typed persistence entity
     *
     * @return Credentials Internal record constructed from this projection
     * @throws \UnexpectedValueException If required columns are malformed
     */
    public function build(): Credentials
    {
        return new Credentials(
            $this->row->getInteger('user_id'),
            $this->row->getString('password_hash')
        );
    }
}
