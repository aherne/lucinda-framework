<?php

namespace Test\Lucinda\Project\STDERR;

use Lucinda\UnitTest\Result;
use Lucinda\UnitTest\Validator;
use Lucinda\Project\STDERR as Errors;
use Test\Lucinda\Project\Support;
use Test\Lucinda\Project\Support\ProjectFixture;

/** Verifies ErrorReporter presentation behavior. */
final class ErrorReporterTest
{
    /**
     * Routes error classifications to the correct logger and request metadata
     *
     * @return Result[] Observed behavior assertions
     */
    public function testSeverityRouting(): array
    {
        $project = new ProjectFixture(dirname(__DIR__, 2));
        $results = [];
        foreach (['normal', 'fatal'] as $name) {
            $project->write('xml/' . $name . '.xml', '<xml><loggers><logger '
                . 'class="Lucinda\Logging\Driver\File\Wrapper" path="'
                . $project->getPath() . '/' . $name
                . '" format="%v|%u|%i|%a|%m"/></loggers></xml>');
        }
        $reporter = new Errors\ErrorReporter(
            [
            'REQUEST_URI' => '/broken', 'REMOTE_ADDR' => '127.0.0.2',
            'HTTP_USER_AGENT' => 'Reporter Agent',
            ],
            $project->getPath() . '/xml/normal',
            $project->getPath() . '/xml/fatal'
        );
        $types = [
            \Lucinda\STDERR\ErrorType::NONE,
            \Lucinda\STDERR\ErrorType::SERVER,
            \Lucinda\STDERR\ErrorType::SYNTAX,
            \Lucinda\STDERR\ErrorType::LOGICAL,
            \Lucinda\STDERR\ErrorType::CLIENT,
        ];
        foreach ($types as $type) {
            $reporter->report(
                $type,
                new \RuntimeException('classification-' . $type->name)
            );
        }
        $normal = file_get_contents($project->getPath() . '/normal.log');
        $results[] = (new Validator\Booleans(
            !str_contains($normal, 'classification-NONE')
        ))->assertTrue(
            'NONE classification emits no entry'
        );
        foreach (['SERVER', 'SYNTAX', 'LOGICAL', 'CLIENT'] as $name) {
            $results[] = (new Validator\Booleans(
                str_contains($normal, 'classification-' . $name)
            ))->assertTrue(
                'Classification reaches the application logger: ' . $name
            );
        }
        $results[] = (new Validator\Booleans(
            str_contains(
                $normal,
                '/broken|127.0.0.2|Reporter Agent'
            )
        ))->assertTrue(
            'Error logs retain captured HTTP request metadata'
        );
        $reporter->report(
            \Lucinda\STDERR\ErrorType::INTERNAL,
            new \RuntimeException('internal-failure'),
            new \RuntimeException('original-failure')
        );
        $fatal = file_get_contents($project->getPath() . '/fatal.log');
        $results[] = (new Validator\Booleans(
            str_contains(
                $fatal,
                'internal-failure'
            ) && str_contains($fatal, 'original-failure')
        ))->assertTrue(
            'Fatal logger receives the internal and original failures'
        );
        $results[] = (new Validator\Booleans(
            !str_contains($normal, 'internal-failure')
        ))->assertTrue(
            'Internal failure bypasses ordinary logger'
        );
        return $results;
    }

    /**
     * Terminates cleanly when a mandatory logger configuration is missing
     *
     * @return Result[] Observed behavior assertions
     */
    public function testInitializationFailure(): array
    {
        $project = new ProjectFixture(dirname(__DIR__, 2));
        $results = [];
        $result = (new Support\PhpProcess($project))->run(
            'new \\Lucinda\\Project\\STDERR\\ErrorReporter([], '
            . 'getcwd() . "/missing", getcwd() . "/also-missing"); echo '
            . '"unreachable";'
        );
        $results[] = (new Validator\Booleans(
            str_contains(
                $result['stdout'],
                'XML to setup error reporting is mandatory'
            )
        ))->assertTrue(
            'Missing logger configuration produces the fallback explanation'
        );
        $results[] = (new Validator\Booleans(
            !str_contains($result['stdout'], 'unreachable')
        ))->assertTrue(
            'Initialization terminates immediately'
        );
        return $results;
    }
}
