<?php

namespace Test\Lucinda\Project\Facets;

use Lucinda\UnitTest\Result;
use Lucinda\UnitTest\Validator;
use Lucinda\Project\Facets;
use Test\Lucinda\Project\Support\ProjectFixture;

/** Verifies Security presentation behavior. */
final class SecurityTest
{
    /**
     * Publishes anonymous defaults and request-local public security values
     *
     * @return Result[] Observed behavior assertions
     */
    private function testRequestContext(): array
    {
        $project = new ProjectFixture(dirname(__DIR__, 2));
        $results = [];
        $facet = new Facets\Security();
        foreach (
            [
                'getUserID',
                'getAccessToken',
                'getCsrfToken',
                'getCallback',
            ] as $getter
        ) {
            $results[] = (new Validator\Booleans(
                $facet->$getter() === null
            ))->assertTrue(
                'Uninitialized security context is anonymous'
            );
        }
        $facet->setUserID(0);
        $facet->setAccessToken('bearer');
        $facet->setCsrfToken('csrf');
        $facet->setCallback('/next?status=success');
        $results[] = (new Validator\Integers(
            $facet->getUserID()
        ))->assertEquals(
            0,
            'Zero remains an authenticated account ID'
        );
        $results[] = (new Validator\Strings(
            $facet->getAccessToken()
        ))->assertEquals(
            'bearer',
            'Bearer token exposed'
        );
        $results[] = (new Validator\Strings(
            $facet->getCsrfToken()
        ))->assertEquals(
            'csrf',
            'CSRF token exposed'
        );
        $results[] = (new Validator\Strings(
            $facet->getCallback()
        ))->assertEquals(
            '/next?status=success',
            'REST callback exposed intact'
        );
        $facet->setUserID('external-id');
        $results[] = (new Validator\Strings(
            $facet->getUserID()
        ))->assertEquals(
            'external-id',
            'String identifiers retain their type'
        );
        $facet->setUserID(null);
        $facet->setAccessToken(null);
        $facet->setCsrfToken(null);
        $results[] = (new Validator\Booleans(
            $facet->getUserID() === null
        ))->assertTrue(
            'Identity may return to anonymous'
        );
        $results[] = (new Validator\Booleans(
            $facet->getAccessToken() === null
        ))->assertTrue(
            'Access token may be cleared'
        );
        $results[] = (new Validator\Booleans(
            $facet->getCsrfToken() === null
        ))->assertTrue(
            'CSRF token may be cleared'
        );
        $results[] = (new Validator\Booleans(
            (new Facets\Security())->getUserID() === null
        ))->assertTrue(
            'Security context is not shared across facet instances'
        );
        return $results;
    }
    /**
     * Verifies setUserID through the complete application contract
     *
     * @return Result[] Assertions from the shared contract scenario
     */
    public function setUserID(): array
    {
        return $this->testRequestContext();
    }

    /**
     * Verifies getUserID through the complete application contract
     *
     * @return Result[] Assertions from the shared contract scenario
     */
    public function getUserID(): array
    {
        return $this->testRequestContext();
    }

    /**
     * Verifies setAccessToken through the complete application contract
     *
     * @return Result[] Assertions from the shared contract scenario
     */
    public function setAccessToken(): array
    {
        return $this->testRequestContext();
    }

    /**
     * Verifies getAccessToken through the complete application contract
     *
     * @return Result[] Assertions from the shared contract scenario
     */
    public function getAccessToken(): array
    {
        return $this->testRequestContext();
    }

    /**
     * Verifies setCsrfToken through the complete application contract
     *
     * @return Result[] Assertions from the shared contract scenario
     */
    public function setCsrfToken(): array
    {
        return $this->testRequestContext();
    }

    /**
     * Verifies getCsrfToken through the complete application contract
     *
     * @return Result[] Assertions from the shared contract scenario
     */
    public function getCsrfToken(): array
    {
        return $this->testRequestContext();
    }

    /**
     * Verifies setCallback through the complete application contract
     *
     * @return Result[] Assertions from the shared contract scenario
     */
    public function setCallback(): array
    {
        return $this->testRequestContext();
    }

    /**
     * Verifies getCallback through the complete application contract
     *
     * @return Result[] Assertions from the shared contract scenario
     */
    public function getCallback(): array
    {
        return $this->testRequestContext();
    }
}
