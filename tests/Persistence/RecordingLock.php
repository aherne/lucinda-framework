<?php

namespace Test\Lucinda\Project\Persistence;

use Lucinda\Project\Persistence\Locking\Lock;

/** Records the ownership requests delegated by a session handler. */
final class RecordingLock implements Lock
{
    /** @var list<string> Ownership requests observed by this fixture */
    private array $events = [];

    /**
     * Records the identity requested by the coordinating handler
     *
     * @param string $identity Session identity requested by the handler
     */
    public function acquire(string $identity): void
    {
        $this->events[] = 'acquire:' . $identity;
    }

    /** Records the end of the coordinated ownership lifecycle. */
    public function release(): void
    {
        $this->events[] = 'release';
    }

    /** @return list<string> Ownership requests in their observed order */
    public function getEvents(): array
    {
        return $this->events;
    }
}
