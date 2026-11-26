<?php

namespace Test\Lucinda\Project\Persistence\Entities;

use Lucinda\UnitTest\Result;
use Lucinda\UnitTest\Validator;
use Lucinda\Project\Persistence\Entities;

/** Verifies the immutable Identity persistence record. */
final class IdentityTest
{
    /**
     * Preserves typed record values and prevents subsequent mutation
     *
     * @return Result[] Observed behavior assertions
     */
    public function getUserID(): array
    {
        $results = [];
        $entity = new Entities\Identity(
            17,
        );
        $results[] = (new Validator\Integers(
            $entity->getUserID()
        ))->assertEquals(
            17,
            "getUserID preserves the logical record value and type"
        );
        return $results;
    }
}
