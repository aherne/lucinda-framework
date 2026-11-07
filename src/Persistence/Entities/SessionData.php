<?php

namespace Lucinda\Project\Persistence\Entities;

/**
 * Serialized payload of one unexpired session
 *
 * Internal immutable persistence data; never pass this entity to presentation.
 *
 * @internal
 */
final readonly class SessionData
{
    /** Serialized bytes belonging to the unexpired session */
    private string $payload;

    /**
     * Captures the validated values for this persistence record
     *
     * @param string $payload Serialized bytes belonging to the unexpired
     *     session
     */
    public function __construct(
        string $payload
    ) {
        $this->payload = $payload;
    }
    /**
     * Gets the serialized bytes of the unexpired session
     *
     * @return string Serialized bytes belonging to the unexpired session
     */
    public function getPayload(): string
    {
        return $this->payload;
    }
}
