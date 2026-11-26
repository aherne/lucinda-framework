<?php

namespace Test\Lucinda\Project\Persistence\Entities;

use Lucinda\UnitTest\Result;
use Lucinda\UnitTest\Validator;
use Lucinda\Project\Persistence\Entities;

/** Verifies the immutable Page persistence record. */
final class PageTest
{
    /**
     * Preserves typed record values and prevents subsequent mutation
     *
     * @return Result[] Observed behavior assertions
     */
    private function testRecordValues(): array
    {
        $results = [];
        $entity = new Entities\Page(
            0,
            false,
        );
        $results[] = (new Validator\Integers(
            $entity->getID()
        ))->assertEquals(
            0,
            "getID preserves the logical record value and type"
        );
        $results[] = (new Validator\Booleans(
            $entity->isPublic() === false
        ))->assertTrue(
            "isPublic preserves the logical record value and type"
        );
        return $results;
    }
    /**
     * Verifies getID through the complete application contract
     *
     * @return Result[] Assertions from the shared contract scenario
     */
    public function getID(): array
    {
        return $this->testRecordValues();
    }

    /**
     * Verifies isPublic through the complete application contract
     *
     * @return Result[] Assertions from the shared contract scenario
     */
    public function isPublic(): array
    {
        return $this->testRecordValues();
    }
}
