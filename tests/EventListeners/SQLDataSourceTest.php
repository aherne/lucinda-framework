<?php

namespace Test\Lucinda\Project\EventListeners;

use Lucinda\UnitTest\Result;
use Lucinda\UnitTest\Validator;
use Lucinda\Project\EventListeners as Listeners;
use Test\Lucinda\Project\Support\ProjectFixture;

/** Verifies SQLDataSource presentation behavior. */
final class SQLDataSourceTest
{
    /**
     * Maps explicit configuration without eagerly accessing a server
     *
     * @return Result[] Observed behavior assertions
     */
    public function run(): array
    {
        $project = new ProjectFixture(dirname(__DIR__, 2));
        $results = [];
        $listener = new Listeners\SQLDataSource(
            $project->getXml('empty.xml')
        );
        $facet = $listener->run();
        $results[] = (new Validator\Booleans(
            $facet instanceof \Lucinda\Framework\SqlConnectionProvider
        ))->assertTrue(
            'Listener publishes the provider facet'
        );
        $results[] = (new Validator\Booleans(
            $listener->run() !== $facet
        ))->assertTrue(
            'Each run owns an independent provider'
        );
        $rejected = false;
        try {
            (new Listeners\SQLDataSource(
                $project->getXml('sql/missing-server.xml')
            ))->run();
        } catch (\Exception) {
            $rejected = true;
        }
        $results[] = (new Validator\Booleans(
            $rejected
        ))->assertTrue(
            'Configured section without a server fails explicitly'
        );
        return $results;
    }

    /**
     * Loads default XML and reports missing files
     *
     * @return Result[] Observed behavior assertions
     */
    public function testConfigurationPath(): array
    {
        $project = new ProjectFixture(dirname(__DIR__, 2));
        $results = [];
        $facet = (new Listeners\SQLDataSource())->run();
        $results[] = (new Validator\Booleans(
            $facet instanceof \Lucinda\Framework\SqlConnectionProvider
        ))->assertTrue(
            'Default path loads the private project configuration'
        );
        unlink($project->getPath() . '/xml/stdout/sql.xml');
        $rejected = false;
        try {
            (new Listeners\SQLDataSource())->run();
        } catch (\Lucinda\Framework\XmlException) {
            $rejected = true;
        }
        $results[] = (new Validator\Booleans(
            $rejected
        ))->assertTrue(
            'Missing default configuration fails explicitly'
        );
        return $results;
    }
}
