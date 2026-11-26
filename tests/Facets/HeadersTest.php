<?php

namespace Test\Lucinda\Project\Facets;

use Lucinda\UnitTest\Result;
use Lucinda\UnitTest\Validator;
use Lucinda\Project\Facets;
use Test\Lucinda\Project\Support\ProjectFixture;

/** Verifies Headers presentation behavior. */
final class HeadersTest
{
    /**
     * Shares wrapper response mutations with other consumers
     *
     * @return Result[] Observed behavior assertions
     */
    private function testSharedPolicy(): array
    {
        $project = new ProjectFixture(dirname(__DIR__, 2));
        $results = [];
        $facet = new Facets\Headers();
        $results[] = (new Validator\Booleans(
            $facet->getWrapper() === null
        ))->assertTrue(
            'Header policy initially unavailable'
        );
        $wrapper = new \Lucinda\Headers\Wrapper(
            new \SimpleXMLElement('<xml><headers/></xml>'),
            'catalog',
            []
        );
        $facet->setWrapper($wrapper);
        $results[] = (new Validator\Arrays(
            [$facet->getWrapper()]
        ))->assertIdentical(
            [$wrapper],
            'Policy wrapper retains identity'
        );
        $wrapper->getResponse()->setCustomHeader('X-Test', 'shared');
        $results[] = (new Validator\Strings(
            $facet->getWrapper()->getResponse()->toArray()['X-Test']
        ))->assertEquals(
            'shared',
            'Response header changes are visible through the facet'
        );
        return $results;
    }
    /**
     * Verifies setWrapper through the complete application contract
     *
     * @return Result[] Assertions from the shared contract scenario
     */
    public function setWrapper(): array
    {
        return $this->testSharedPolicy();
    }

    /**
     * Verifies getWrapper through the complete application contract
     *
     * @return Result[] Assertions from the shared contract scenario
     */
    public function getWrapper(): array
    {
        return $this->testSharedPolicy();
    }
}
