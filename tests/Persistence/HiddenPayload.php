<?php

namespace Test\Lucinda\Project\Persistence;

/** Payload fixture proving that private fields are included in validation. */
class HiddenPayload
{
    /**
     * Captures a private value that presentation must still inspect
     *
     * @param object $hidden Nested payload owned by this fixture
     */
    public function __construct(private object $hidden)
    {
    }
}
