<?php

namespace Test\Lucinda\Project\EventListeners;

use Lucinda\UnitTest\Result;
use Lucinda\UnitTest\Validator;
use Lucinda\Project\Facets;
use Lucinda\Project\EventListeners as Listeners;
use Lucinda\Project\Cacheables;
use Test\Lucinda\Project\Support\ProjectFixture;
use Test\Lucinda\Project\Support\HttpRequest;

/** Verifies HttpCaching presentation behavior. */
final class HttpCachingTest
{
    /**
     * Preserves bodies and accumulates cache headers only for GET requests
     *
     * @return Result[] Observed behavior assertions
     */
    private function testNormalResponse(): array
    {
        $project = new ProjectFixture(dirname(__DIR__, 2));
        $results = [];
        $factory = new HttpRequest();
        $facet = new Facets\Headers();
        $listener = new Listeners\HttpCaching($factory->create(), $facet);
        $results[] = (new Validator\Strings(
            $listener->transform('body')
        ))->assertEquals(
            'body',
            'No headers policy preserves the body'
        );
        $results[] = (new Validator\Arrays(
            $listener->getExtraHeaders()->get()
        ))->assertIdentical(
            [],
            'No policy adds no headers'
        );
        $facet->setWrapper(new \Lucinda\Headers\Wrapper(
            $project->getXml('headers/cacheable.xml'),
            'catalog',
            []
        ));
        $listener = new Listeners\HttpCaching($factory->create(), $facet);
        $results[] = (new Validator\Strings(
            $listener->transform('body')
        ))->assertEquals(
            'body',
            'Cacheable GET retains its rendered body'
        );
        $headers = $listener->getExtraHeaders()->get();
        $results[] = (new Validator\Booleans(
            isset($headers['ETag'])
        ))->assertTrue(
            'Cacheable GET emits its entity tag'
        );
        $results[] = (new Validator\Booleans(
            isset($headers['Cache-Control'])
        ))->assertTrue(
            'Cache policy contributes response headers'
        );
        $post = new Listeners\HttpCaching($factory->create('POST'), $facet);
        $results[] = (new Validator\Strings(
            $post->transform('posted')
        ))->assertEquals(
            'posted',
            'POST bypasses response caching'
        );
        $results[] = (new Validator\Arrays(
            $post->getExtraHeaders()->get()
        ))->assertIdentical(
            [],
            'POST accumulates no cache headers'
        );
        return $results;
    }

    /**
     * Terminates matching validators and failed preconditions with cache
     * headers
     *
     * @return Result[] Observed behavior assertions
     */
    public function testConditionalResponses(): array
    {
        $project = new ProjectFixture(dirname(__DIR__, 2));
        $results = [];
        $factory = new HttpRequest();
        $tag = (new Cacheables\Etag($factory->create(), 'body'))->getEtag();
        foreach (
            [
                ['If-None-Match', $tag, 304],
                ['If-Match', 'different', 412],
            ] as [$name, $value, $status]
        ) {
            $facet = new Facets\Headers();
            $facet->setWrapper(new \Lucinda\Headers\Wrapper(
                $project->getXml('headers/conditional.xml'),
                'catalog',
                [$name => $value]
            ));
            $listener = new Listeners\HttpCaching($factory->create(), $facet);
            $response = null;
            try {
                $listener->transform('body');
            } catch (\Lucinda\MVC\TerminationException $error) {
                $response = $error->getResponse();
            }
            $results[] = (new Validator\Booleans(
                $response !== null
            ))->assertTrue(
                'Conditional outcome terminates normal rendering'
            );
            if ($response !== null) {
                $property = new \ReflectionProperty($response, 'status');
                $results[] = (new Validator\Arrays(
                    [$property->getValue($response)->value]
                ))->assertIdentical(
                    [$status],
                    'Termination carries the required conditional HTTP status'
                );
                $property = new \ReflectionProperty($response, 'headers');
                $results[] = (new Validator\Booleans(
                    isset($property->getValue($response)->get()['ETag'])
                ))->assertTrue(
                    'Conditional response retains cache validation headers'
                );
            }
        }
        return $results;
    }
    /**
     * Verifies transform through the complete application contract
     *
     * @return Result[] Assertions from the shared contract scenario
     */
    public function transform(): array
    {
        return $this->testNormalResponse();
    }

    /**
     * Verifies getExtraHeaders through the complete application contract
     *
     * @return Result[] Assertions from the shared contract scenario
     */
    public function getExtraHeaders(): array
    {
        return $this->testNormalResponse();
    }
}
