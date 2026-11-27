<?php

namespace Test\Lucinda\Project\EventListeners;

use Lucinda\UnitTest\Result;
use Lucinda\UnitTest\Validator;
use Lucinda\Project\EventListeners as Listeners;
use Lucinda\Project\Facets;
use Test\Lucinda\Project\Support\ProjectFixture;

/** Verifies FileDB presentation behavior. */
final class FileDBTest
{
    /**
     * Builds a shared file database facet from explicit configuration
     *
     * @return Result[] Observed behavior assertions
     */
    public function run(): array
    {
        $project = new ProjectFixture(dirname(__DIR__, 2));
        $results = [];
        $xml = $project->getXml('filedb/schema.xml');
        $xml->lucinda_db->schemas->schema = $project->getPath() . '/filedb';
        $listener = new Listeners\FileDB($xml);
        $facet = $listener->run();
        $results[] = (new Validator\Booleans(
            $facet instanceof Facets\FileDB
        ))->assertTrue(
            'File database listener publishes its facet'
        );
        $results[] = (new Validator\Booleans(
            $facet->getWrapper() instanceof \Lucinda\DB\Wrapper
        ))->assertTrue(
            'Facet contains the initialized wrapper'
        );
        $results[] = (new Validator\Booleans(
            !is_dir($project->getPath() . '/filedb')
        ))->assertTrue(
            'Listener initialization creates no database folders'
        );
        $project->write('xml/stdout/file_db.xml', $xml->asXML());
        $results[] = (new Validator\Booleans(
            (new Listeners\FileDB())->run()
            instanceof Facets\FileDB
        ))->assertTrue(
            'Default file path loads the same configuration'
        );
        $rejected = false;
        try {
            (new Listeners\FileDB($project->getXml('empty.xml')))->run();
        } catch (\Lucinda\DB\ConfigurationException) {
            $rejected = true;
        }
        $results[] = (new Validator\Booleans(
            $rejected
        ))->assertTrue(
            'Missing schemas fail explicitly'
        );
        return $results;
    }
}
