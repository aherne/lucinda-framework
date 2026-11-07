<?php

namespace Lucinda\Project\Persistence\Entities;

/**
 * One role assigned to a local account
 *
 * Internal immutable persistence data; never pass this entity to presentation.
 *
 * @internal
 */
final readonly class Role
{
    /** Persisted role name compared against route policy */
    private string $name;

    /**
     * Captures the validated values for this persistence record
     *
     * @param string $name Persisted role name compared against route policy
     */
    public function __construct(
        string $name
    ) {
        $this->name = $name;
    }
    /**
     * Gets the persisted role name
     *
     * @return string Persisted role name compared against route policy
     */
    public function getName(): string
    {
        return $this->name;
    }
}
