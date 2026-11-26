<?php

namespace Test\Lucinda\Project\Facets;

use Lucinda\UnitTest\Result;
use Lucinda\UnitTest\Validator;
use Lucinda\Project\Facets;
use Test\Lucinda\Project\Support\ProjectFixture;

/** Verifies Loggers presentation behavior. */
final class LoggersTest
{
    /**
     * Exposes only the logger published to the current facet
     *
     * @return Result[] Observed behavior assertions
     */
    private function testSharedLogger(): array
    {
        $project = new ProjectFixture(dirname(__DIR__, 2));
        $results = [];
        $facet = new Facets\Loggers();
        $results[] = (new Validator\Booleans(
            $facet->getLogger() === null
        ))->assertTrue(
            'Logger initially unavailable'
        );
        $wrapper = new \Lucinda\Logging\Wrapper(
            new \SimpleXMLElement('<xml/>'),
            new \Lucinda\Logging\RequestInformation()
        );
        $logger = $wrapper->getLogger();
        $facet->setLogger($logger);
        $results[] = (new Validator\Arrays(
            [$facet->getLogger()]
        ))->assertIdentical(
            [$logger],
            'Published logger retains identity'
        );
        $results[] = (new Validator\Booleans(
            (new Facets\Loggers())->getLogger() === null
        ))->assertTrue(
            'Different request does not inherit the logger'
        );
        return $results;
    }
    /**
     * Verifies setLogger through the complete application contract
     *
     * @return Result[] Assertions from the shared contract scenario
     */
    public function setLogger(): array
    {
        return $this->testSharedLogger();
    }

    /**
     * Verifies getLogger through the complete application contract
     *
     * @return Result[] Assertions from the shared contract scenario
     */
    public function getLogger(): array
    {
        return $this->testSharedLogger();
    }
}
