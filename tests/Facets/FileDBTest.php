<?php

namespace Test\Lucinda\Project\Facets;

use Lucinda\UnitTest\Result;
use Lucinda\UnitTest\Validator;
use Lucinda\Project\Facets;
use Test\Lucinda\Project\Support\ProjectFixture;

/** Verifies FileDB presentation behavior. */
final class FileDBTest
{
    /**
     * Publishes the exact configured file database wrapper
     *
     * @return Result[] Observed behavior assertions
     */
    private function testSharedDatabase(): array
    {
        $project = new ProjectFixture(dirname(__DIR__, 2));
        $results = [];
        $facet = new Facets\FileDB();
        $xml = $project->getXml('filedb/schema.xml');
        $xml->lucinda_db->schemas->schema = $project->getPath() . '/filedb';
        $wrapper = new \Lucinda\DB\Wrapper($xml);
        $facet->setWrapper($wrapper);
        $results[] = (new Validator\Arrays(
            [$facet->getWrapper()]
        ))->assertIdentical(
            [$wrapper],
            'Configured wrapper is retained'
        );
        $results[] = (new Validator\Booleans(
            $facet->getWrapper()->getSchemaDriver()
                instanceof \Lucinda\DB\SchemaOperations
        ))->assertTrue(
            'Consumers can obtain the configured schema operations'
        );
        return $results;
    }
    /**
     * Verifies setWrapper through the complete application contract
     *
     * @return Result[] Assertions from the shared contract scenario
     */
    public function setWrapper(): array
    {
        return $this->testSharedDatabase();
    }

    /**
     * Verifies getWrapper through the complete application contract
     *
     * @return Result[] Assertions from the shared contract scenario
     */
    public function getWrapper(): array
    {
        return $this->testSharedDatabase();
    }
}
