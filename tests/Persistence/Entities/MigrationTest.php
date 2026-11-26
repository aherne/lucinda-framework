<?php

namespace Test\Lucinda\Project\Persistence\Entities;

use Lucinda\UnitTest\Result;
use Lucinda\UnitTest\Validator;
use Lucinda\Project\Persistence\Entities;

/** Verifies the immutable Migration persistence record. */
final class MigrationTest
{
    /**
     * Preserves typed record values and prevents subsequent mutation
     *
     * @return Result[] Observed behavior assertions
     */
    private function testRecordValues(): array
    {
        $results = [];
        $entity = new Entities\Migration(
            'App\\Migration',
            \Lucinda\Migration\Status::FAILED,
        );
        $results[] = (new Validator\Strings(
            $entity->getClassName()
        ))->assertEquals(
            'App\\Migration',
            "getClassName preserves the logical record value and type"
        );
        $results[] = (new Validator\Arrays(
            [$entity->getStatus()]
        ))->assertIdentical(
            [\Lucinda\Migration\Status::FAILED],
            "getStatus preserves the logical record value and type"
        );
        return $results;
    }
    /**
     * Verifies getClassName through the complete application contract
     *
     * @return Result[] Assertions from the shared contract scenario
     */
    public function getClassName(): array
    {
        return $this->testRecordValues();
    }

    /**
     * Verifies getStatus through the complete application contract
     *
     * @return Result[] Assertions from the shared contract scenario
     */
    public function getStatus(): array
    {
        return $this->testRecordValues();
    }
}
