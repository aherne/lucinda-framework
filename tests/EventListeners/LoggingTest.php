<?php

namespace Test\Lucinda\Project\EventListeners;

use Lucinda\UnitTest\Result;
use Lucinda\UnitTest\Validator;
use Lucinda\Project\EventListeners as Listeners;
use Lucinda\Project\Facets;
use Test\Lucinda\Project\Support\ProjectFixture;
use Test\Lucinda\Project\Support\HttpRequest;

/** Verifies Logging presentation behavior. */
final class LoggingTest
{
    /**
     * Creates a working logger with request-specific metadata
     *
     * @return Result[] Observed behavior assertions
     */
    public function run(): array
    {
        $project = new ProjectFixture(dirname(__DIR__, 2));
        $results = [];
        $request = (new HttpRequest())->create(
            headers: ["User-Agent" => "Unit Agent"]
        );
        $xml = $project->getXml('logging/request.xml');
        $xml->loggers->logger['path'] = $project->getPath() . '/request-log';
        $project->write('xml/stdout/loggers.xml', $xml->asXML());
        $listener = new Listeners\Logging($request);
        $facet = $listener->run();
        $results[] = (new Validator\Booleans(
            $facet instanceof Facets\Loggers
        ))->assertTrue(
            'Logging listener publishes its facet'
        );
        $facet->getLogger()->info('sample');
        $output = file_get_contents($project->getPath() . '/request-log.log');
        $results[] = (new Validator\Booleans(
            str_contains($output, 'catalog')
        ))->assertTrue(
            'Log entry includes the request route'
        );
        $results[] = (new Validator\Booleans(
            str_contains($output, 'sample')
        ))->assertTrue(
            'Published logger writes application messages'
        );

        $results[] = (new Validator\Booleans(
            str_contains(
                $output,
                '127.0.0.1'
            ) && str_contains($output, 'Unit Agent')
        ))->assertTrue(
            'HTTP logger includes captured client and user-agent metadata'
        );

        return $results;
    }

    /**
     * Initializes logging when the HTTP client supplies no user agent
     *
     * @return Result[] Default request metadata assertions
     */
    public function testMissingUserAgent(): array
    {
        $project = new ProjectFixture(dirname(__DIR__, 2));
        $results = [];
        $request = (new HttpRequest())->create();
        $facet = (new Listeners\Logging($request))->run();
        $results[] = (new Validator\Booleans(
            $facet->getLogger() instanceof \Lucinda\Logging\Logger
        ))->assertTrue(
            'Missing optional user-agent header does not prevent logging'
        );
        return $results;
    }
}
