<?php

namespace Test\Lucinda\Project\ViewResolvers;

use Lucinda\UnitTest\Result;
use Lucinda\UnitTest\Validator;
use Lucinda\Project\ViewResolvers as Resolvers;
use Test\Lucinda\Project\Support\ProjectFixture;

/** Verifies Console presentation behavior. */
final class ConsoleTest
{
    /**
     * Renders application values before interpreting console markup
     *
     * @return Result[] Observed behavior assertions
     */
    public function resolve(): array
    {
        $project = new ProjectFixture(dirname(__DIR__, 2));
        $results = [];
        $project->write(
            'templates/views/unit-console.html',
            '<b>${data.name}</b>'
        );
        $resolver = new Resolvers\Console();
        $output = $resolver->resolve(
            new \Lucinda\MVC\Response\View(
                ['name' => 'Console'],
                'unit-console'
            )
        );
        $results[] = (new Validator\Booleans(
            str_contains($output, 'Console')
        ))->assertTrue(
            'Console template receives view data'
        );
        $results[] = (new Validator\Booleans(
            !str_contains($output, '<b>')
        ))->assertTrue(
            'Console markup is compiled'
        );
        $results[] = (new Validator\Booleans(
            str_contains($output, "\033[")
        ))->assertTrue(
            'Console rendering emits ANSI controls'
        );
        return $results;
    }
}
