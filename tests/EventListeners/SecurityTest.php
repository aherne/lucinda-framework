<?php

namespace Test\Lucinda\Project\EventListeners;

use Lucinda\UnitTest\Result;
use Lucinda\UnitTest\Validator;
use Lucinda\Project\EventListeners as Listeners;
use Lucinda\Project\Facets;
use Lucinda\WebSecurity;
use Lucinda\WebSecurity\Security\MultiFactorAuthentication\ResultStatus;
use Test\Lucinda\Project\Support\ProjectFixture;
use Test\Lucinda\Project\Support\HttpRequest;

/** Verifies Security presentation behavior. */
final class SecurityTest
{
    /**
     * Runs configured security and publishes public guest context without
     * storage
     *
     * @return Result[] Observed behavior assertions
     */
    public function run(): array
    {
        $project = new ProjectFixture(dirname(__DIR__, 2));
        $results = [];

        $project->installXml(
            'xml/stdout/security.xml',
            'security/workflow.xml'
        );
        $app = new \Lucinda\STDOUT\Application(
            $project->getPath() . '/stdout.xml'
        );

        $request = (new HttpRequest())->create();
        $validated = new \Lucinda\STDOUT\Validators\ValidatedRequest(
            $app,
            $request
        );
        $listener = new Listeners\Security(
            $request,
            $validated,
            new \Lucinda\STDOUT\Cookies(),
            new \Lucinda\STDOUT\Session()
        );
        $facets = $listener->run()->getAll();
        $security = $facets[Facets\Security::class];
        $mfa = $facets[Facets\MultiFactor::class];
        $results[] = (new Validator\Booleans(
            $security->getUserID() === null
        ))->assertTrue(
            'Public guest request has no authenticated ID'
        );
        $results[] = (new Validator\Booleans(
            $security->getAccessToken() === null
        ))->assertTrue(
            'Guest has no bearer persistence token'
        );
        $results[] = (new Validator\Booleans(
            $security->getCsrfToken() === null
        ))->assertTrue(
            'Public guest without an actionable packet has no CSRF outcome'
        );
        $results[] = (new Validator\Booleans(
            $mfa->getPacket() === null
        ))->assertTrue(
            'Public guest request has no MFA form'
        );
        $results[] = (new Validator\Booleans(
            $security->getCallback() === null
        ))->assertTrue(
            'Allowed public request has no navigation target'
        );
        return $results;
    }

    /**
     * Redirects unauthenticated access to a private route with caching disabled
     *
     * @return Result[] Observed behavior assertions
     */
    public function testGuestRedirect(): array
    {
        $project = new ProjectFixture(dirname(__DIR__, 2));
        $results = [];

        $project->installXml(
            'xml/stdout/security.xml',
            'security/workflow.xml'
        );
        $app = new \Lucinda\STDOUT\Application(
            $project->getPath() . '/stdout.xml'
        );

        $request = (new HttpRequest())->create(uri: '/private');
        $validated = new \Lucinda\STDOUT\Validators\ValidatedRequest(
            $app,
            $request
        );
        $listener = new Listeners\Security(
            $request,
            $validated,
            new \Lucinda\STDOUT\Cookies(),
            new \Lucinda\STDOUT\Session()
        );
        $response = null;
        try {
            $listener->run();
        } catch (\Lucinda\MVC\TerminationException $error) {
            $response = $error->getResponse();
        }
        $results[] = (new Validator\Booleans(
            $response instanceof \Lucinda\MVC\Response\Redirect
        ))->assertTrue(
            'Private guest request requires a browser redirect'
        );
        $location = (
            new \ReflectionProperty($response, 'location')
        )->getValue($response);
        $results[] = (new Validator\Booleans(
            str_starts_with($location, '/login')
        ))->assertTrue(
            'Redirect selects the guest login callback'
        );
        $results[] = (new Validator\Booleans(
            str_contains($location, 'status=')
        ))->assertTrue(
            'Security redirect includes its outcome status'
        );
        $results[] = (new Validator\Booleans(
            (
            new \ReflectionProperty($response, 'preventCaching')
            )->getValue($response) === true
        ))->assertTrue(
            'Authentication navigation explicitly disables caching'
        );
        return $results;
    }

    /**
     * Publishes enrollment forms only on their designated route
     *
     * @return Result[] Observed behavior assertions
     */
    public function testMfaFormSelection(): array
    {
        $project = new ProjectFixture(dirname(__DIR__, 2));
        $results = [];

        $project->installXml(
            'xml/stdout/security.xml',
            'security/workflow.xml'
        );
        $app = new \Lucinda\STDOUT\Application(
            $project->getPath() . '/stdout.xml'
        );

        $request = (new HttpRequest())->create();
        $validated = new \Lucinda\STDOUT\Validators\ValidatedRequest(
            $app,
            $request
        );
        $listener = new Listeners\Security(
            $request,
            $validated,
            new \Lucinda\STDOUT\Cookies(),
            new \Lucinda\STDOUT\Session()
        );
        $method = new \ReflectionMethod(
            $listener,
            'checkMultiFactorAuthentication'
        );
        $packet = new WebSecurity\Packets\MultiFactor();
        $packet->setStatus(
            ResultStatus::SETUP_REQUIRED
        );
        $packet->setCallback('/catalog');
        $results[] = (new Validator\Arrays(
            [$method->invoke($listener, $packet)]
        ))->assertIdentical(
            [$packet],
            'Setup packet on its form route is exposed for rendering'
        );
        $packet->setCallback('/other');
        $results[] = (new Validator\Booleans(
            $method->invoke($listener, $packet) === null
        ))->assertTrue(
            'Setup packet on another route remains a navigation outcome'
        );
        $packet->setCallback('/catalog');
        $packet->setStatus(
            ResultStatus::REQUIRED
        );
        $results[] = (new Validator\Arrays(
            [$method->invoke($listener, $packet)]
        ))->assertIdentical(
            [$packet],
            'Challenge packet on its form route is exposed for rendering'
        );
        return $results;
    }
}
