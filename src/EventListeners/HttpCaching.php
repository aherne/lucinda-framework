<?php

namespace Lucinda\Project\EventListeners;

use Lucinda\Framework\CacheableFinder;
use Lucinda\Framework\XmlDetector;
use Lucinda\MVC\EventListener;
use Lucinda\MVC\Response\Blank;
use Lucinda\MVC\Response\ByStatus;
use Lucinda\MVC\Response\Headers as ResponseHeaders;
use Lucinda\MVC\Response\HttpStatus;
use Lucinda\MVC\Response\Transformer\Body;
use Lucinda\MVC\Response\Transformer\Headers as HeadersTransformer;
use Lucinda\MVC\TerminationException;
use Lucinda\Project\Facets\Headers;
use Lucinda\STDOUT\Request;
use Lucinda\STDOUT\Request\Method;

/**
 * Validates the rendered representation against HTTP cache conditions
 *
 * Successful validation preserves the body and supplies cache headers.
 * Conditional responses terminate processing through specialized responses.
 */
final class HttpCaching implements EventListener, Body, HeadersTransformer
{
    const XML_PATH = "xml/stdout/headers";
    
    /**
     * Request whose conditional headers are being evaluated
     */
    private Request $request;

    /**
     * Shared HTTP headers wrapper for the current request
     */
    private Headers $headers;

    /**
     * Headers to add when the rendered response continues normally
     */
    private ResponseHeaders $extraHeaders;

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
        $this->extraHeaders = new ResponseHeaders();
    }

    /**
     * Validates the rendered body and preserves it for normal responses
     *
     * @param string $source Rendered response body
     * @return string Unchanged body when cache validation permits delivery
     * @throws TerminationException If a conditional response is required
     */
    public function transform(string $source): string
    {
        $wrapper = $this->headers->getWrapper();
        if ($wrapper === null || $this->request->getMethod() !== Method::GET) {
            return $source;
        }

        $detector = new XmlDetector(dirname(__DIR__, 2).DIRECTORY_SEPARATOR.self::XML_PATH);

        // Proposed signature: XML, request, rendered body.
        $finder = new CacheableFinder(
            $detector->getXML(),
            $this->request,
            $source
        );

        $status = HttpStatus::from(
            $wrapper->validateCache(
                $finder->getResult(),
                $this->request->getMethod()->value
            )
        );

        $headers = $wrapper->getResponse()->toArray();

        if ($status !== HttpStatus::OK) {
            throw new TerminationException($this->getTerminationResponse($status, $headers));
        }

        foreach ($headers as $name => $value) {
            $this->extraHeaders->add($name, $value);
        }

        return $source;
    }

    /**
     * Gets cache headers accumulated during body transformation
     *
     * @return ResponseHeaders Headers added to the normal response
     */
    public function getExtraHeaders(): ResponseHeaders
    {
        return $this->extraHeaders;
    }

    /**
     * Constructs response to terminate with
     * 
     * @param HttpStatus $status
     * @param array $headers
     * @throws \LogicException
     * @return Blank|ByStatus
     */
    private function getTerminationResponse(HttpStatus $status, array $headers): Blank|ByStatus
    {
        $response = match ($status) {
            HttpStatus::NOT_MODIFIED => new Blank($status),
            HttpStatus::PRECONDITION_FAILED => new ByStatus($status),
            default => throw new \LogicException(
                "Unexpected cache validation status: ".$status->value
            ),
        };

        foreach ($headers as $name => $value) {
            $response->setHeader($name, $value);
        }
        return $response;
    }
}