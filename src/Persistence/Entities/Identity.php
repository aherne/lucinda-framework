<?php

namespace Lucinda\Project\Persistence\Entities;

use Lucinda\Framework\Persistence\Entities\Entity;

/**
 * Local account resolved from one provider identity
 *
 * Internal immutable persistence data; never pass this entity to presentation.
 *
 * @internal
 */
final readonly class Identity implements Entity
{
    /** Eligible local account linked to the provider identity */
    private int $userID;

    /**
     * Captures the validated values for this persistence record
     *
     * @param int $userID Eligible local account linked to the provider identity
     */
    public function __construct(
        int $userID
    ) {
        $this->userID = $userID;
    }
    /**
     * Gets the eligible local account identifier
     *
     * @return int Eligible local account linked to the provider identity
     */
    public function getUserID(): int
    {
        return $this->userID;
    }
}
