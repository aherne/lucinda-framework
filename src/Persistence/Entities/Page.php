<?php

namespace Lucinda\Project\Persistence\Entities;

/**
 * Persisted resource identifier and public access policy
 *
 * Internal immutable persistence data; never pass this entity to presentation.
 *
 * @internal
 */
final readonly class Page
{
    /** Persisted resource identifier used for permission checks */
    private int $id;

    /** Whether the resource permits unauthenticated access */
    private bool $isPublic;

    /**
     * Captures the validated values for this persistence record
     *
     * @param int $id Persisted resource identifier used for permission checks
     * @param bool $isPublic Whether the resource permits unauthenticated access
     */
    public function __construct(
        int $id,
        bool $isPublic
    ) {
        $this->id = $id;
        $this->isPublic = $isPublic;
    }
    /**
     * Gets the persisted resource identifier
     *
     * @return int Persisted resource identifier used for permission checks
     */
    public function getID(): int
    {
        return $this->id;
    }

    /**
     * Checks whether the resource permits unauthenticated access
     *
     * @return bool Whether the resource permits unauthenticated access
     */
    public function isPublic(): bool
    {
        return $this->isPublic;
    }
}
