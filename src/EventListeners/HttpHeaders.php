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
 * Sets up HTTP Headers API for later cache/cors validation or request/response headers operations
 */
class HttpHeaders implements Faceted
{
    const XML_PATH = "xml/stdout/headers";
    
    private Request $request;
    private ValidatedRequest $validatedRequest;

    public function __construct(Request $request, ValidatedRequest $validatedRequest)
    {
        $this->validatedRequest = $validatedRequest;
        $this->request = $request;
    }

    public function run(): Facet
    {
        $detector = new XmlDetector(dirname(__DIR__, 2).DIRECTORY_SEPARATOR.self::XML_PATH);
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
