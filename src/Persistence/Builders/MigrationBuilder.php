<?php

namespace Lucinda\Project\Persistence\Builders;

use Lucinda\Project\Persistence\Entities\Migration;

/**
 * Builds a Migration entity from its selected database projection
 *
 * The builder owns one row and performs explicit type validation when building.
 *
 * @internal
 */
final class MigrationBuilder
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
     * @return Migration Internal record constructed from this projection
     * @throws \UnexpectedValueException If required columns are malformed
     */
    public function build(): Migration
    {
        switch ($this->row->getInteger('status_code')) {
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
        return new Migration($this->row->getString('class_name'), $status);
    }
}
