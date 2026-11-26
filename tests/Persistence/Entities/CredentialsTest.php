<?php

namespace Test\Lucinda\Project\Persistence\Entities;

use Lucinda\UnitTest\Result;
use Lucinda\UnitTest\Validator;
use Lucinda\Project\Persistence\Entities;

/** Verifies the immutable Credentials persistence record. */
final class CredentialsTest
{
    /**
     * Preserves typed record values and prevents subsequent mutation
     *
     * @return Result[] Observed behavior assertions
     */
    private function testRecordValues(): array
    {
        $results = [];
        $entity = new Entities\Credentials(
            0,
            'hash-with-\\-bytes',
        );
        $results[] = (new Validator\Integers(
            $entity->getUserID()
        ))->assertEquals(
            0,
            "getUserID preserves the logical record value and type"
        );
        $results[] = (new Validator\Strings(
            $entity->getPasswordHash()
        ))->assertEquals(
            'hash-with-\\-bytes',
            "getPasswordHash preserves the logical record value and type"
        );
        return $results;
    }
    /**
     * Verifies getUserID through the complete application contract
     *
     * @return Result[] Assertions from the shared contract scenario
     */
    public function getUserID(): array
    {
        return $this->testRecordValues();
    }

    /**
     * Verifies getPasswordHash through the complete application contract
     *
     * @return Result[] Assertions from the shared contract scenario
     */
    public function getPasswordHash(): array
    {
        return $this->testRecordValues();
    }
}
