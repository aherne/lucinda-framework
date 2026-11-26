<?php

namespace Test\Lucinda\Project\Cacheables;

use Lucinda\UnitTest\Result;
use Lucinda\UnitTest\Validator;
use Lucinda\Project\Cacheables;
use Test\Lucinda\Project\Support\ProjectFixture;
use Test\Lucinda\Project\Support\HttpRequest;

/** Verifies Etag presentation behavior. */
final class EtagTest
{
    /**
     * Varies entity tags with host and response bytes while remaining stable
     *
     * @return Result[] Observed behavior assertions
     */
    public function testRepresentationIdentity(): array
    {
        $project = new ProjectFixture(dirname(__DIR__, 2));
        $results = [];
        $factory = new HttpRequest();
        $request = $factory->create();
        $first = new Cacheables\Etag($request, "body\0bytes");
        $same = new Cacheables\Etag($request, "body\0bytes");
        $changed = new Cacheables\Etag($request, 'changed');
        $otherHost = new Cacheables\Etag(
            $factory->create(host: 'other.test'),
            "body\0bytes"
        );
        $results[] = (new Validator\Arrays(
            [$same->getEtag()]
        ))->assertIdentical(
            [$first->getEtag()],
            'Same representation has a stable validator'
        );
        $results[] = (new Validator\Booleans(
            $first->getEtag() !== $changed->getEtag()
        ))->assertTrue(
            'Changed bytes change the validator'
        );
        $results[] = (new Validator\Booleans(
            $first->getEtag() !== $otherHost->getEtag()
        ))->assertTrue(
            'Different host has a different validator'
        );
        $results[] = (new Validator\Integers(
            $first->getTime()
        ))->assertEquals(
            0,
            'ETag policy leaves Last-Modified unset'
        );
        return $results;
    }
}
