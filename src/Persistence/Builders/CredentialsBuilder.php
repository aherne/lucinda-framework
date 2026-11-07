<?php

namespace Lucinda\Project\Persistence\Builders;

use Lucinda\Project\Persistence\Entities\Credentials;

/**
 * Builds a Credentials entity from its selected database projection
 *
 * The builder owns one row and performs explicit type validation when building.
 *
 * @internal
 */
final class CredentialsBuilder
{
    /** Projection owned by this builder */
    private Row $row;

    /**
     * Captures the database row to convert
     *
     * @param Row $row Selected record with validated column access
     */
    public function __construct(Row $row)
    {
        $this->row = $row;
    }

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
