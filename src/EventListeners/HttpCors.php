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
 * Performs CORS validation of HTTP request headers for OPTIONS request and sends response accordingly
 */
class HttpCors implements UnFaceted
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
     * Captures the dependencies needed for response cache validation
     *
     * @param Request $request Current HTTP request
     * @param Headers $headers Shared HTTP headers facet
     */
    public function __construct(Request $request, Headers $headers)
    {
        $this->request = $request;
        $this->headers = $headers;
    }

    public function run(): void
    {
        $validator = $this->headers->getWrapper();
        if ($validator===null || $this->request->getMethod()!=Method::OPTIONS) {
            return;
        }

        // perform CORS validation
        $validator->validateCors($this->request->getProtocol()->value."://".$this->request->getServer()->getName());

        // send response immediately
        throw new TerminationException($this->getTerminationResponse($validator->getResponse()->toArray()));
    }

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
