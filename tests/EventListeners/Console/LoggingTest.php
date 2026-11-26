<?php

namespace Test\Lucinda\Project\EventListeners\Console;

use Lucinda\UnitTest\Result;
use Lucinda\UnitTest\Validator;
use Lucinda\Project\EventListeners as Listeners;
use Lucinda\Project\Facets;
use Test\Lucinda\Project\Support\ProjectFixture;

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
        $project = new ProjectFixture(dirname(__DIR__, 3));
        $results = [];
        $server = $_SERVER;
        try {
            $_SERVER['argv'] = ['index.php', 'check'];
            $request = new \Lucinda\ConsoleSTDOUT\Request();
        } finally {
            $_SERVER = $server;
        }
        $project->write('xml/stdout/loggers.xml', '<xml><loggers><logger '
            . 'class="Lucinda\Logging\Driver\File\Wrapper" path="'
            . $project->getPath() . '/request-log" format="%u|%i|%a|%m"/>'
            . '</loggers></xml>');
        $listener = new Listeners\Console\Logging($request);
        $facet = $listener->run();
        $results[] = (new Validator\Booleans(
            $facet instanceof Facets\Loggers
        ))->assertTrue(
            'Logging listener publishes its facet'
        );
        $facet->getLogger()->info('sample');
        $output = file_get_contents($project->getPath() . '/request-log.log');
        $results[] = (new Validator\Booleans(
            str_contains($output, 'check')
        ))->assertTrue(
            'Log entry includes the request route'
        );
        $results[] = (new Validator\Booleans(
            str_contains($output, 'sample')
        ))->assertTrue(
            'Published logger writes application messages'
        );

        return $results;
    }
}
