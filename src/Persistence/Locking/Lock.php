<?php

namespace Lucinda\Project\Persistence\Locking;

/**
 * Defines exclusive ownership of one identity until explicit release
 *
 * Reacquiring the same identity through one lock owner is idempotent.
 * Switching identities releases the previous lock before acquisition.
 */
interface Lock
{
    /**
     * Acquires exclusive ownership of the requested identity
     *
     * @param string $identity Identity to protect for this operation
     * @throws \Throwable If exclusive ownership cannot be established
     */
    public function acquire(string $identity): void;

    /**
     * Releases the identity currently owned by this lock instance
     *
     * @throws \Throwable If an owned lock cannot be released
     */
    public function release(): void;
}
