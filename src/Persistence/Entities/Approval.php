<?php

namespace Lucinda\Project\Persistence\Entities;

/**
 * Approval decision for one provider identity
 *
 * Internal immutable persistence data; never pass this entity to presentation.
 *
 * @internal
 */
final readonly class Approval
{
    /** Persisted approval decision for the provider identity */
    private string $status;

    /**
     * Captures the validated values for this persistence record
     *
     * @param string $status Persisted approval decision for the provider
     *     identity
     */
    public function __construct(
        string $status
    ) {
        $this->status = $status;
    }
    /**
     * Gets the recorded approval decision
     *
     * @return string Persisted approval decision for the provider identity
     */
    public function getStatus(): string
    {
        return $this->status;
    }
}
