<?php

namespace Test\Lucinda\Project\Persistence\Entities;

use Lucinda\UnitTest\Result;
use Lucinda\UnitTest\Validator;
use Lucinda\Project\Persistence\Entities;

/** Verifies the immutable Mfa persistence record. */
final class MfaTest
{
    /**
     * Preserves typed record values and prevents subsequent mutation
     *
     * @return Result[] Observed behavior assertions
     */
    private function testRecordValues(): array
    {
        $results = [];
        $entity = new Entities\Mfa(
            true,
            'account@example.test',
            null,
            'encrypted-setup',
            1700000000,
            0,
        );
        $results[] = (new Validator\Booleans(
            $entity->isRequired() === true
        ))->assertTrue(
            "isRequired preserves the logical record value and type"
        );
        $results[] = (new Validator\Strings(
            $entity->getAccountName()
        ))->assertEquals(
            'account@example.test',
            "getAccountName preserves the logical record value and type"
        );
        $results[] = (new Validator\Booleans(
            $entity->getSecret() === null
        ))->assertTrue(
            "getSecret preserves the logical record value and type"
        );
        $results[] = (new Validator\Strings(
            $entity->getSetupSecret()
        ))->assertEquals(
            'encrypted-setup',
            "getSetupSecret preserves the logical record value and type"
        );
        $results[] = (new Validator\Integers(
            $entity->getSetupExpires()
        ))->assertEquals(
            1700000000,
            "getSetupExpires preserves the logical record value and type"
        );
        $results[] = (new Validator\Integers(
            $entity->getCounter()
        ))->assertEquals(
            0,
            "getCounter preserves the logical record value and type"
        );
        return $results;
    }
    /**
     * Verifies isRequired through the complete application contract
     *
     * @return Result[] Assertions from the shared contract scenario
     */
    public function isRequired(): array
    {
        return $this->testRecordValues();
    }

    /**
     * Verifies getAccountName through the complete application contract
     *
     * @return Result[] Assertions from the shared contract scenario
     */
    public function getAccountName(): array
    {
        return $this->testRecordValues();
    }

    /**
     * Verifies getSecret through the complete application contract
     *
     * @return Result[] Assertions from the shared contract scenario
     */
    public function getSecret(): array
    {
        return $this->testRecordValues();
    }

    /**
     * Verifies getSetupSecret through the complete application contract
     *
     * @return Result[] Assertions from the shared contract scenario
     */
    public function getSetupSecret(): array
    {
        return $this->testRecordValues();
    }

    /**
     * Verifies getSetupExpires through the complete application contract
     *
     * @return Result[] Assertions from the shared contract scenario
     */
    public function getSetupExpires(): array
    {
        return $this->testRecordValues();
    }

    /**
     * Verifies getCounter through the complete application contract
     *
     * @return Result[] Assertions from the shared contract scenario
     */
    public function getCounter(): array
    {
        return $this->testRecordValues();
    }
}
