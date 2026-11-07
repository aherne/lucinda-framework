<?php

namespace Lucinda\Project\Persistence\Builders;

use Lucinda\Project\Persistence\Entities\Mfa;

/**
 * Builds an MFA enrollment entity from its selected database projection
 *
 * The builder owns one row and performs explicit type validation when building.
 *
 * @internal
 */
final class MfaBuilder
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
     * @return Mfa Internal record constructed from this projection
     * @throws \UnexpectedValueException If required columns are malformed
     */
    public function build(): Mfa
    {
        return new Mfa(
            $this->row->getBoolean('mfa_required'),
            $this->row->getString('account_name'),
            $this->row->getNullableString('secret'),
            $this->row->getNullableString('setup_secret'),
            $this->row->getNullableInteger('setup_expires'),
            $this->row->getNullableInteger('last_counter')
        );
    }
}
