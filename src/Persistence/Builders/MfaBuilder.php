<?php

namespace Lucinda\Project\Persistence\Builders;

use Lucinda\Framework\Persistence\Builders\Builder;
use Lucinda\Framework\Persistence\Builders\Row;
use Lucinda\Project\Persistence\Entities\Mfa;

/**
 * Builds an MFA enrollment entity from its selected database projection
 *
 * Each call validates its supplied row and builds an independent entity.
 *
 * @internal
 */
final class MfaBuilder implements Builder
{
    /**
     * Converts the selected values into the typed persistence entity
     *
     * @param Row $row Selected projection with typed column access
     * @return Mfa Internal record constructed from this projection
     * @throws \Lucinda\Framework\Persistence\Queries\ValidationException
     *     If required columns are missing or malformed
     */
    public function build(Row $row): Mfa
    {
        return new Mfa(
            $row->getBoolean('mfa_required'),
            $row->getString('account_name'),
            $row->getString('secret'),
            $row->getString('setup_secret'),
            $row->getInteger('setup_expires'),
            $row->getInteger('last_counter')
        );
    }
}
