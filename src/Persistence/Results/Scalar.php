<?php

namespace Lucinda\Project\Persistence\Results;

/**
 * Describes the outcome of one completed database write
 *
 * Affected-row counts support atomic acceptance checks. The inserted identifier
 * is meaningful only for writes that create an auto-incremented record.
 *
 * @internal
 */
final readonly class Scalar
{
    /** Number of records changed by this completed operation */
    private int $affected;

    /** Generated record identifier, meaningful for an inserted record */
    private int $insertID;

    /**
     * Captures the write metadata supplied by the database
     *
     * @param int $affected Number of records changed by this operation
     * @param int $insertID Generated record identifier, when applicable
     */
    public function __construct(
        int $affected,
        int $insertID
    ) {
        $this->affected = $affected;
        $this->insertID = $insertID;
    }

    /**
     * Gets the affected-row count captured when this operation completed
     *
     * @return int Number of records changed by this operation
     */
    public function getAffectedRows(): int
    {
        return $this->affected;
    }

    /**
     * Gets the generated identifier captured when this operation completed
     *
     * @return int Insert identifier, meaningful for auto-incremented records
     */
    public function getInsertId(): int
    {
        return $this->insertID;
    }
}
