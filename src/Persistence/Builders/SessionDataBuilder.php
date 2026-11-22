<?php

namespace Lucinda\Project\Persistence\Builders;

use Lucinda\Framework\Persistence\Builders\Builder;
use Lucinda\Framework\Persistence\Builders\Row;
use Lucinda\Framework\Persistence\Queries\ValidationException;
use Lucinda\Project\Persistence\Entities\SessionData;

/**
 * Builds a SessionData entity from its selected database projection
 *
 * Each call validates its supplied row and builds an independent entity.
 *
 * @internal
 */
final class SessionDataBuilder implements Builder
{
    /**
     * Converts the selected values into the typed persistence entity
     *
     * @param Row $row Selected projection with typed column access
     * @return SessionData Internal record constructed from this projection
     * @throws \Lucinda\Framework\Persistence\Queries\ValidationException
     *     If required columns are missing or malformed
     */
    public function build(Row $row): SessionData
    {
        $payload = $row->getStreamed('payload');
        if (is_resource($payload)) {
            $payload = stream_get_contents($payload);
        }
        if (!is_string($payload)) {
            throw new ValidationException(
                'string', get_debug_type($payload), 'payload'
            );
        }
        return new SessionData($payload);
    }
}
