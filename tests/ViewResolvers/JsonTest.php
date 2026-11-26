<?php

namespace Test\Lucinda\Project\ViewResolvers;

use Lucinda\UnitTest\Result;
use Lucinda\UnitTest\Validator;
use Lucinda\Project\ViewResolvers as Resolvers;
use Test\Lucinda\Project\Support\ProjectFixture;

/** Verifies Json presentation behavior. */
final class JsonTest
{
    /**
     * Encodes Unicode and nested application values without losing their types
     *
     * @return Result[] Observed behavior assertions
     */
    public function resolve(): array
    {
        $project = new ProjectFixture(dirname(__DIR__, 2));
        $results = [];
        $data = ['name' => 'Éva', 'items' => [0, false, null, ['ok' => true]]];
        $resolver = new Resolvers\Json();
        $output = $resolver->resolve(new \Lucinda\MVC\Response\View($data));
        $results[] = (new Validator\Arrays(
            json_decode($output, true, 512, JSON_THROW_ON_ERROR)
        ))->assertIdentical(
            ['status' => 'ok', 'body' => $data],
            'JSON envelope preserves every application value'
        );
        $results[] = (new Validator\Arrays(
            json_decode(
                $resolver->resolve(new \Lucinda\MVC\Response\View([])),
                true
            )
        ))->assertIdentical(
            ['status' => 'ok', 'body' => []],
            'Empty application data retains the response envelope'
        );
        return $results;
    }

    /**
     * Marks calls originating in errors-mvc as errors
     *
     * @return Result[] Observed behavior assertions
     */
    public function testErrorEnvelope(): array
    {
        $project = new ProjectFixture(dirname(__DIR__, 2));
        $results = [];
        $project->write(
            'vendor/lucinda/errors-mvc/json-caller.php',
            '<?php return '
            . '(new \\Lucinda\\Project\\ViewResolvers\\Json())->resolve('
            . 'new \\Lucinda\\MVC\\Response\\View(["message" => "failure"]));'
        );
        $caller = $project->getPath()
            . '/vendor/lucinda/errors-mvc/json-caller.php';
        $output = require $caller;
        $results[] = (new Validator\Arrays(
            json_decode($output, true, 512, JSON_THROW_ON_ERROR)
        ))->assertIdentical(
            ['status' => 'error', 'body' => ['message' => 'failure']],
            'STDERR rendering uses the error status envelope'
        );
        return $results;
    }

    /**
     * Rejects recursive values instead of returning invalid JSON output
     *
     * @return Result[] Encoding failure assertions
     */
    public function testRecursiveData(): array
    {
        $project = new ProjectFixture(dirname(__DIR__, 2));
        $results = [];
        $data = [];
        $data['self'] = &$data;
        $rejected = false;
        try {
            (new Resolvers\Json())->resolve(
                new \Lucinda\MVC\Response\View($data)
            );
        } catch (\JsonException) {
            $rejected = true;
        }
        $results[] = (new Validator\Booleans(
            $rejected
        ))->assertTrue(
            'Recursive data fails through the JSON API'
        );
        return $results;
    }
}
