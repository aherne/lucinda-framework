<?php

namespace Lucinda\Project\Persistence\Builders;

use Lucinda\Framework\Persistence\Builders\Builder;
use Lucinda\Framework\Persistence\Builders\Row;
use Lucinda\Project\Persistence\Entities\Migration;

/**
 * Builds a Migration entity from its selected database projection
 *
 * Each call validates its supplied row and builds an independent entity.
 *
 * @internal
 */
final class MigrationBuilder implements Builder
{
    /**
     * Converts the selected values into the typed persistence entity
     *
     * @param Row $row Selected projection with typed column access
     * @return Migration Internal record constructed from this projection
     * @throws \Lucinda\Framework\Persistence\Queries\ValidationException
     *     If required columns are missing or malformed
     */
    public function build(Row $row): Migration
    {
        switch ($row->getInteger('status_code')) {
            case 1:
                $status = \Lucinda\Migration\Status::PENDING;
                break;
            case 2:
                $status = \Lucinda\Migration\Status::FAILED;
                break;
            case 3:
                $status = \Lucinda\Migration\Status::PASSED;
                break;
            default:
                throw new \UnexpectedValueException('Invalid migration status');
        }
        return new Migration($row->getString('class_name'), $status);
    }
}
