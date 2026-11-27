<?php

namespace Test\Lucinda\Project\EventListeners;

use Lucinda\UnitTest\Result;
use Lucinda\UnitTest\Validator;
use Lucinda\Project\Facets;
use Lucinda\Project\EventListeners as Listeners;
use Test\Lucinda\Project\Support\ProjectFixture;
use Test\Lucinda\Project\Support\HttpRequest;

/** Verifies HttpCors presentation behavior. */
final class HttpCorsTest
{
    /**
     * Terminates OPTIONS with validated CORS headers while allowing ordinary
     * requests
     *
     * @return Result[] Observed behavior assertions
     */
    public function run(): array
    {
        $project = new ProjectFixture(dirname(__DIR__, 2));
        $results = [];
        $factory = new HttpRequest();
        $facet = new Facets\Headers();
        $plain = new Listeners\HttpCors($factory->create(), $facet);
        $plain->run();
        $results[] = (new Validator\Booleans(
            true
        ))->assertTrue(
            'Requests without header policy continue'
        );
        $wrapper = new \Lucinda\Headers\Wrapper(
            $project->getXml('headers/cors.xml'),
            'catalog',
            [
                'Origin' => 'https://app.test',
                'Access-Control-Request-Method' => 'GET',
            ]
        );
        $facet->setWrapper($wrapper);
        (new Listeners\HttpCors($factory->create(), $facet))->run();
        $results[] = (new Validator\Booleans(
            true
        ))->assertTrue(
            'Non-OPTIONS requests continue'
        );
        $response = null;
        try {
            (
                new Listeners\HttpCors($factory->create('OPTIONS'), $facet)
            )->run();
        } catch (\Lucinda\MVC\TerminationException $error) {
            $response = $error->getResponse();
        }
        $results[] = (new Validator\Booleans(
            $response instanceof \Lucinda\MVC\Response\Blank
        ))->assertTrue(
            'Preflight terminates with an empty response'
        );
        $property = new \ReflectionProperty($response, 'status');
        $results[] = (new Validator\Integers(
            $property->getValue($response)->value
        ))->assertEquals(
            204,
            'Preflight returns no-content status'
        );
        $property = new \ReflectionProperty($response, 'headers');
        $headers = $property->getValue($response)->get();
        $results[] = (new Validator\Strings(
            $headers['Access-Control-Allow-Origin']
        ))->assertEquals(
            'https://app.test',
            'Preflight includes the configured allowed origin'
        );
        $results[] = (new Validator\Strings(
            $headers['Content-Type']
        ))->assertEquals(
            'application/json',
            'Preflight content type is explicit'
        );
        return $results;
    }
}
