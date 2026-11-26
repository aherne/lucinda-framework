<?php

namespace Test\Lucinda\Project\Persistence\Entities;

use Lucinda\UnitTest\Result;
use Lucinda\UnitTest\Validator;
use Lucinda\Project\Persistence\Entities;

/** Verifies the immutable SessionData persistence record. */
final class SessionDataTest
{
    /**
     * Preserves typed record values and prevents subsequent mutation
     *
     * @return Result[] Observed behavior assertions
     */
    public function getPayload(): array
    {
        $results = [];
        $entity = new Entities\SessionData(
            "a\0\xffb",
        );
        $results[] = (new Validator\Strings(
            $entity->getPayload()
        ))->assertEquals(
            "a\0\xffb",
            "getPayload preserves the logical record value and type"
        );
        return $results;
    }
}
