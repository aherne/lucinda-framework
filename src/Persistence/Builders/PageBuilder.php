<?php

namespace Lucinda\Project\Persistence\Builders;

use Lucinda\Framework\Persistence\Builders\Builder;
use Lucinda\Framework\Persistence\Builders\Row;
use Lucinda\Project\Persistence\Entities\Page;

/**
 * Builds a Page entity from its selected database projection
 *
 * Each call validates its supplied row and builds an independent entity.
 *
 * @internal
 */
final class PageBuilder implements Builder
{
    /**
     * Converts the selected values into the typed persistence entity
     *
     * @param Row $row Selected projection with typed column access
     * @return Page Internal record constructed from this projection
     * @throws \Lucinda\Framework\Persistence\Queries\ValidationException
     * If required columns are missing or malformed
     */
    public function build(Row $row): Page
    {
        return new Page(
            $row->getInteger('id'),
            $row->getBoolean('is_public')
        );
    }
}
