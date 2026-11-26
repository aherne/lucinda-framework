<?php

namespace Lucinda\Project\EventListeners;

use Lucinda\Framework\XmlDetector;
use Lucinda\Headers\Wrapper;
use Lucinda\MVC\EventListener\Faceted;
use Lucinda\MVC\Facet;
use Lucinda\STDOUT\Request;
use Lucinda\STDOUT\Validators\ValidatedRequest;
use Lucinda\Project\Facets\Headers as HeadersFacet;

/**
 * Publishes HTTP header policy and response state for the validated route
 *
 * Each run loads default and route-specific policies, captures incoming HTTP
 * headers, and shares the initialized wrapper through a request facet. Cache
 * and CORS listeners use that same wrapper to accumulate response headers.
 */
final class HttpHeaders implements Faceted
{
    /** Project-relative configuration path without its XML extension */
    public const XML_PATH = "xml/stdout/headers";

    /**
     * Request retained for listener execution and metadata extraction
     */
    private Request $request;
    /**
     * Validated route and requested response format
     */
    private ValidatedRequest $validatedRequest;

    /**
     * Captures the request and route used to select HTTP header policies
     *
     * @param Request $request Current request and incoming headers
     * @param ValidatedRequest $validatedRequest Resolved route for policy
     * lookup
     */
    public function __construct(
        Request $request,
        ValidatedRequest $validatedRequest
    ) {
        $this->validatedRequest = $validatedRequest;
        $this->request = $request;
    }

    /**
     * Loads HTTP header policies and publishes the initialized wrapper
     *
     * @return HeadersFacet Request facet sharing the headers wrapper
     * @throws \Throwable If configuration loading or wrapper setup fails
     */
    public function run(): Facet
    {
        $detector = new XmlDetector(
            dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . self::XML_PATH
        );
        $wrapper = new Wrapper(
            $detector->getXML(),
            $this->validatedRequest->getRoute(),
            $this->request->headers()
        );
        $facet = new HeadersFacet();
        $facet->setWrapper($wrapper);
        return $facet;
    }
}
