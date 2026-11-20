<?php

namespace Lucinda\Project\Persistence\Builders;

use Lucinda\Framework\Persistence\Builders\Builder;
use Lucinda\Project\Persistence\Entities\Page;

/**
 * Builds a Page entity from its selected database projection
 *
 * The builder owns one row and performs explicit type validation when building.
 *
 * @internal
 */
final class PageBuilder extends Builder
{
    /**
     * Converts the selected values into the typed persistence entity
     *
     * @return Page Internal record constructed from this projection
     * @throws \UnexpectedValueException If required columns are malformed
     */
    public function build(): Page
    {
        return new Page(
            $this->row->getInteger('id'),
            $this->row->getBoolean('is_public')
        );
    }
}
