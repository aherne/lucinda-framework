<?php

namespace Test\Lucinda\Project\Persistence\Entities;

use Lucinda\UnitTest\Result;
use Lucinda\UnitTest\Validator;
use Lucinda\Project\Persistence\Entities;

/** Verifies the immutable Role persistence record. */
final class RoleTest
{
    /**
     * Preserves typed record values and prevents subsequent mutation
     *
     * @return Result[] Observed behavior assertions
     */
    public function getName(): array
    {
        $results = [];
        $entity = new Entities\Role(
            'ÉDITORS',
        );
        $results[] = (new Validator\Strings(
            $entity->getName()
        ))->assertEquals(
            'ÉDITORS',
            "getName preserves the logical record value and type"
        );
        return $results;
    }
}
