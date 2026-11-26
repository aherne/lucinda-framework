<?php

namespace Test\Lucinda\Project\ViewResolvers;

use Lucinda\UnitTest\Result;
use Lucinda\UnitTest\Validator;
use Lucinda\Project\ViewResolvers as Resolvers;
use Test\Lucinda\Project\Support\ProjectFixture;

/** Verifies Html presentation behavior. */
final class HtmlTest
{
    /**
     * Renders successive views with their own application values
     *
     * @return Result[] Observed behavior assertions
     */
    public function resolve(): array
    {
        $project = new ProjectFixture(dirname(__DIR__, 2));
        $results = [];
        $project->write(
            'templates/views/unit-html.html',
            '<p>${data.name}</p>'
        );
        $resolver = new Resolvers\Html();
        $first = $resolver->resolve(
            new \Lucinda\MVC\Response\View(['name' => 'Éva'], 'unit-html')
        );
        $second = $resolver->resolve(
            new \Lucinda\MVC\Response\View(['name' => 'Other'], 'unit-html')
        );
        $results[] = (new Validator\Booleans(
            str_contains($first, '<p>Éva</p>')
        ))->assertTrue(
            'HTML template receives the view data'
        );
        $results[] = (new Validator\Booleans(
            str_contains($second, '<p>Other</p>')
        ))->assertTrue(
            'Compiled template uses current data on reuse'
        );
        $results[] = (new Validator\Booleans(
            !str_contains($second, 'Éva')
        ))->assertTrue(
            'Rendering does not leak previous view data'
        );
        return $results;
    }

    /**
     * Reports a missing template configuration through the framework exception
     *
     * @return Result[] Observed behavior assertions
     */
    public function testMissingConfiguration(): array
    {
        $project = new ProjectFixture(dirname(__DIR__, 2));
        $results = [];
        unlink($project->getPath() . '/xml/templating.xml');
        $rejected = false;
        try {
            (new Resolvers\Html())->resolve(
                new \Lucinda\MVC\Response\View([], 'unit-html')
            );
        } catch (\Lucinda\Framework\XmlException) {
            $rejected = true;
        }
        $results[] = (new Validator\Booleans(
            $rejected
        ))->assertTrue(
            'Missing HTML configuration fails explicitly'
        );
        return $results;
    }
}
