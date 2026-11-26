<?php

namespace Test\Lucinda\Project\Persistence\Entities;

use Lucinda\UnitTest\Result;
use Lucinda\UnitTest\Validator;
use Lucinda\Project\Persistence\Entities;

/** Verifies the immutable Approval persistence record. */
final class ApprovalTest
{
    /**
     * Preserves typed record values and prevents subsequent mutation
     *
     * @return Result[] Observed behavior assertions
     */
    public function getStatus(): array
    {
        $results = [];
        $entity = new Entities\Approval(
            'pending',
        );
        $results[] = (new Validator\Strings(
            $entity->getStatus()
        ))->assertEquals(
            'pending',
            "getStatus preserves the logical record value and type"
        );
        return $results;
    }
}
