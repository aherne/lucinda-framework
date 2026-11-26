<?php

namespace Test\Lucinda\Project\Controllers;

use Lucinda\UnitTest\Result;
use Lucinda\UnitTest\Validator;
use Lucinda\Project\Controllers;
use Lucinda\Project\STDERR as Errors;
use Lucinda\Project\ViewResolvers as Resolvers;
use Test\Lucinda\Project\Support\ProjectFixture;

/** Verifies Error presentation behavior. */
final class ErrorTest
{
    /**
     * Selects the debug view for HTML, JSON and console errors
     *
     * @return Result[] Observed behavior assertions
     */
    public function run(): array
    {
        $project = new ProjectFixture(dirname(__DIR__, 2));
        $results = [];
        $app = new \Lucinda\STDERR\Application(
            $project->getPath() . '/stderr.xml'
        );
        $error = new \RuntimeException('controller-failure');
        $request = new \Lucinda\STDERR\Request(
            $app->getRoutes('default'),
            $error
        );
        foreach (
            [
                'html' => 'debug',
                'json' => '',
                'console' => 'debug-console',
            ] as $format => $template
        ) {
            $validated = new \Lucinda\STDERR\Service\ValidatedRequest(
                $app,
                $error,
                $format
            );
            $controller = new Controllers\Error(
                new Errors\DisplayErrors(),
                $request,
                $validated
            );
            $view = $controller->run();
            $results[] = (new Validator\Arrays(
                [$view->getFile()]
            ))->assertIdentical(
                [$template],
                'Requested format selects the matching debug template'
            );
            $results[] = (new Validator\Strings(
                $view->getData()['message']
            ))->assertEquals(
                'controller-failure',
                'Exception message reaches debug data'
            );
            $results[] = (new Validator\Arrays(
                [$view->getData()['type']]
            ))->assertIdentical(
                [\RuntimeException::class],
                'Exception type retained'
            );
            // The default policy enables diagnostics. Exercise the public
            // error branch independently of that concrete policy.
            (
                new \ReflectionProperty($controller, 'displayErrors')
            )->setValue($controller, false);
            $view = $controller->run();
            if ($format === 'json') {
                $results[] = (new Validator\Arrays(
                    $view->getData()
                ))->assertIdentical(
                    ['message' => 'Internal Server Error'],
                    'Public JSON response exposes no diagnostics'
                );
            } else {
                $results[] = (new Validator\Arrays(
                    [$view->getFile()]
                ))->assertIdentical(
                    [$format === 'html' ? '500' : '500-console'],
                    'Public response selects the generic error template'
                );
                $results[] = (new Validator\Arrays(
                    $view->getData()
                ))->assertIdentical(
                    [],
                    'Public template receives no exception details'
                );
            }
        }
        return $results;
    }

    /**
     * Retains original template locations and the underlying PHP failure
     *
     * @return Result[] Observed behavior assertions
     */
    public function testTemplateDiagnostics(): array
    {
        $project = new ProjectFixture(dirname(__DIR__, 2));
        $results = [];
        $project->write('templates/views/broken-unit.html', '${data.missing}');
        $error = null;
        try {
            (new Resolvers\Html())->resolve(
                new \Lucinda\MVC\Response\View([], 'broken-unit')
            );
        } catch (\Lucinda\Templating\ViewException $failure) {
            $error = $failure;
        }
        $results[] = (new Validator\Booleans(
            $error instanceof \Lucinda\Templating\ViewException
        ))->assertTrue(
            'Invalid view produces a template-aware exception'
        );
        $app = new \Lucinda\STDERR\Application(
            $project->getPath() . '/stderr.xml'
        );
        $controller = new Controllers\Error(
            new Errors\DisplayErrors(),
            new \Lucinda\STDERR\Request($app->getRoutes('default'), $error),
            new \Lucinda\STDERR\Service\ValidatedRequest($app, $error, 'json')
        );
        $data = $controller->run()->getData();
        $results[] = (new Validator\Booleans(
            str_ends_with(
                $data['template']['file'],
                '/broken-unit.html'
            )
        ))->assertTrue(
            'Debug payload reports the original template path'
        );
        $results[] = (new Validator\Integers(
            $data['template']['line']
        ))->assertEquals(
            1,
            'Debug payload reports the source line'
        );
        $results[] = (new Validator\Arrays(
            [$data['file']]
        ))->assertIdentical(
            [$data['template']['file']],
            'Primary location uses source template'
        );
        $results[] = (new Validator\Booleans(
            isset($data['cause']['message'])
        ))->assertTrue(
            'Underlying runtime failure remains available'
        );
        return $results;
    }
}
