<?php

namespace Lucinda\Project\Persistence\Entities;

use Lucinda\Migration\Status;

/**
 * Recorded execution status of one migration class
 *
 * Internal immutable persistence data; never pass this entity to presentation.
 *
 * @internal
 */
final readonly class Migration
{
    /** Fully qualified migration class owning this result */
    private string $className;

    /** Recorded pending, failed or passed execution status */
    private Status $status;

    /**
     * Captures the validated values for this persistence record
     *
     * @param string $className Fully qualified migration class owning this
     *     result
     * @param Status $status Recorded pending, failed or passed execution status
     */
    public function __construct(
        string $className,
        Status $status
    ) {
        $this->className = $className;
        $this->status = $status;
    }
    /**
     * Gets the migration class owning this recorded result
     *
     * @return string Fully qualified migration class owning this result
     */
    public function getClassName(): string
    {
        return $this->className;
    }

    /**
     * Gets the recorded migration execution status
     *
     * @return Status Recorded pending, failed or passed execution status
     */
    public function getStatus(): Status
    {
        return $this->status;
    }
}
