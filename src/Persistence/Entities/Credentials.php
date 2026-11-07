<?php

namespace Lucinda\Project\Persistence\Entities;

/**
 * Credentials selected for one active local account
 *
 * Internal immutable persistence data; never pass this entity to presentation.
 *
 * @internal
 */
final readonly class Credentials
{
    /** Local account owning the selected credentials */
    private int $userID;

    /** Stored password verifier retained only inside authentication */
    private string $passwordHash;

    /**
     * Captures the validated values for this persistence record
     *
     * @param int $userID Local account owning the selected credentials
     * @param string $passwordHash Stored password verifier retained only inside
     *     authentication
     */
    public function __construct(
        int $userID,
        string $passwordHash
    ) {
        $this->userID = $userID;
        $this->passwordHash = $passwordHash;
    }
    /**
     * Gets the local account owning these credentials
     *
     * @return int Local account owning the selected credentials
     */
    public function getUserID(): int
    {
        return $this->userID;
    }

    /**
     * Gets the stored password verifier
     *
     * @return string Stored password verifier used inside authentication
     */
    public function getPasswordHash(): string
    {
        return $this->passwordHash;
    }
}
