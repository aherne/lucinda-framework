<?php

namespace Test\Lucinda\Project\EventListeners;

use Lucinda\UnitTest\Result;
use Lucinda\UnitTest\Validator;
use Lucinda\Project\EventListeners as Listeners;
use Test\Lucinda\Project\Support\ProjectFixture;
use Test\Lucinda\Project\Support\RedisFixture;

/** Verifies NoSQLDataSource presentation behavior. */
final class NoSQLDataSourceTest
{
    /**
     * Maps explicit configuration and connects the Date storage provider
     *
     * @return Result[] Observed behavior assertions
     */
    public function run(): array
    {
        $project = new ProjectFixture(dirname(__DIR__, 2));
        $redis = new RedisFixture();
        $results = [];
        $listener = new Listeners\NoSQLDataSource($redis->getConfiguration());
        $facet = $listener->run();
        $results[] = (new Validator\Booleans(
            $facet instanceof \Lucinda\Framework\NoSqlDriverProvider
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
            (new Listeners\NoSQLDataSource(new \SimpleXMLElement(
                '<xml><nosql><invalid/></nosql></xml>'
            )))->run();
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
        $facet = (new Listeners\NoSQLDataSource())->run();
        $results[] = (new Validator\Booleans(
            $facet instanceof \Lucinda\Framework\NoSqlDriverProvider
        ))->assertTrue(
            'Default path loads the private project configuration'
        );
        unlink($project->getPath() . '/xml/stdout/nosql.xml');
        $rejected = false;
        try {
            (new Listeners\NoSQLDataSource())->run();
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
