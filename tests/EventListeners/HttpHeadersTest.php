<?php

namespace Test\Lucinda\Project\EventListeners;

use Lucinda\UnitTest\Result;
use Lucinda\UnitTest\Validator;
use Lucinda\Project\EventListeners as Listeners;
use Lucinda\Project\Facets;
use Test\Lucinda\Project\Support\ProjectFixture;
use Test\Lucinda\Project\Support\HttpRequest;

/** Verifies HttpHeaders presentation behavior. */
final class HttpHeadersTest
{
    /**
     * Combines configured route policies with captured request headers
     *
     * @return Result[] Observed behavior assertions
     */
    public function run(): array
    {
        $project = new ProjectFixture(dirname(__DIR__, 2));
        $results = [];
        $request = (new HttpRequest())->create(
            headers: ['If-None-Match' => 'tag']
        );
        $app = new \Lucinda\STDOUT\Application(
            $project->getPath() . '/stdout.xml'
        );
        $validated = new \Lucinda\STDOUT\Validators\ValidatedRequest(
            $app,
            $request
        );
        $listener = new Listeners\HttpHeaders($request, $validated);
        $facet = $listener->run();
        $results[] = (new Validator\Booleans(
            $facet instanceof Facets\Headers
        ))->assertTrue(
            'Listener publishes the HTTP headers facet'
        );
        $results[] = (new Validator\Strings(
            $facet->getWrapper()->getRequest()->getIfNoneMatch()
        ))->assertEquals(
            'tag',
            'Captured conditional request header reaches the wrapper'
        );
        return $results;
    }
}
