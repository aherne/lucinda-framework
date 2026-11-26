<?php

namespace Test\Lucinda\Project\EventListeners;

use Lucinda\UnitTest\Result;
use Lucinda\UnitTest\Validator;
use Test\Lucinda\Project\Support;
use Test\Lucinda\Project\Support\ProjectFixture;
use Test\Lucinda\Project\Support\HttpRequest;

/** Verifies Error presentation behavior. */
final class ErrorTest
{
    /**
     * Selects the active STDERR handler output format
     *
     * @return Result[] Observed behavior assertions
     */
    public function run(): array
    {
        $project = new ProjectFixture(dirname(__DIR__, 2));
        $results = [];
        $process = new Support\PhpProcess($project);
        $result = $process->run(<<<'CODE'

        $handler = new \Lucinda\STDERR\FrontController(
            getcwd() . '/stderr.xml', getcwd(),
            new \Lucinda\Project\STDERR\ErrorReporter(
                [],
                getcwd() . '/xml/stderr/loggers',
                getcwd() . '/xml/stderr/loggers-fatal'
            ),
            new \Lucinda\Project\STDERR\FatalErrorResolver(
                getcwd() . '/xml/stderr/application'
            ),
            new \Lucinda\Project\STDERR\DisplayErrors()
        );

        $request = (new \Test\Lucinda\Project\Support\HttpRequest())->create();
        $app = new \Lucinda\STDOUT\Application(getcwd() . '/stdout.xml');
        $validated = new \Lucinda\STDOUT\Validators\ValidatedRequest(
            $app,
            $request
        );
        (new \Lucinda\Project\EventListeners\Error($validated))->run();

        $property = new \ReflectionProperty($handler, 'displayFormat');
        echo $property->getValue($handler);

        CODE);
        $results[] = (new Validator\Integers(
            $result['status']
        ))->assertEquals(
            0,
            'Listener completes in an isolated handler process'
        );
        $results[] = (new Validator\Strings(
            $result['stdout']
        ))->assertEquals(
            'json',
            'Active error handler receives the selected format'
        );
        $results[] = (new Validator\Strings(
            $result['stderr']
        ))->assertEquals(
            '',
            'Error-format selection emits no diagnostics'
        );
        return $results;
    }
}
