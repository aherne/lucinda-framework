<?php

namespace Lucinda\Project\EventListeners;

use Lucinda\MVC\EventListener\UnFaceted;
use Lucinda\MVC\Response\Blank;
use Lucinda\MVC\Response\HttpStatus;
use Lucinda\MVC\TerminationException;
use Lucinda\STDOUT\Request\Method;
use Lucinda\STDOUT\Request;
use Lucinda\Project\Facets\Headers;

/**
 * Applies configured CORS policy to HTTP preflight requests
 *
 * Requests continue when no headers wrapper is available or the method is not
 * OPTIONS. A validated preflight terminates through a no-content response with
 * the wrapper's accumulated CORS headers.
 */
final class HttpCors implements UnFaceted
{
    /**
     * Request whose conditional headers are being evaluated
     */
    private Request $request;

    /**
     * Shared HTTP headers wrapper for the current request
     */
    private Headers $headers;

    /**
     * Captures the request and shared policy for CORS preflight validation
     *
     * @param Request $request Current HTTP request
     * @param Headers $headers Shared HTTP headers facet
     */
    public function __construct(Request $request, Headers $headers)
    {
        $this->request = $request;
        $this->headers = $headers;
    }

    /**
     * Validates OPTIONS requests and terminates with a CORS response
     *
     * Requests without a headers wrapper and non-OPTIONS requests continue
     * unchanged.
     *
     * @return void
     * @throws TerminationException If an OPTIONS response is ready
     * @throws \Throwable If CORS policy validation fails
     */
    public function run(): void
    {
        $validator = $this->headers->getWrapper();
        if (
            $validator === null
            || $this->request->getMethod() != Method::OPTIONS
        ) {
            return;
        }

        // perform CORS validation
        $validator->validateCors(
            $this->request->getProtocol()->value . "://"
            . $this->request->getServer()->getName()
        );

        // send response immediately
        throw new TerminationException($this->getTerminationResponse(
            $validator->getResponse()->toArray()
        ));
    }

    /**
     * Builds the empty preflight response with validated CORS headers
     *
     * @param array<string, string> $headers Validated headers to send
     * @return Blank No-content response carrying the CORS headers
     */
    private function getTerminationResponse(array $headers): Blank
    {
        $response = new Blank(HttpStatus::NO_CONTENT);
        $response->setHeader("Content-Type", "application/json");
        foreach ($headers as $name => $value) {
            $response->setHeader($name, $value);
        }
        return $response;
    }
}
