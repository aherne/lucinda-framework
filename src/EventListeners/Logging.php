<?php

namespace Lucinda\Project\EventListeners;

use Lucinda\Framework\XmlDetector;
use Lucinda\Logging\RequestInformation;
use Lucinda\MVC\EventListener\Faceted;
use Lucinda\Project\Facets\Loggers as LoggersFacet;
use Lucinda\Logging\Wrapper;
use Lucinda\MVC\Facet;
use Lucinda\STDOUT\Request;

/**
 * Sets up Logging API to use in logging later on
 */
class Logging implements Faceted
{
    const XML_PATH = "xml/stdout/loggers";
    
    private Request $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function run(): Facet
    {

        $detector = new XmlDetector(dirname(__DIR__, 2).DIRECTORY_SEPARATOR.self::XML_PATH);
        $wrapper = new Wrapper(
            $detector->getXML(),
            $this->getRequestInformation()
        );
        $facet = new LoggersFacet();
        $facet->setLogger($wrapper->getLogger());
        return $facet;
    }

    private function getRequestInformation(): RequestInformation
    {
        $requestInformation = new RequestInformation();
        $requestInformation->setUri($this->request->getURI()->getPage());
        $requestInformation->setUserAgent($this->request->headers("User-Agent"));
        $requestInformation->setIpAddress($this->request->getClient()->getIP());
        return $requestInformation;
    }
}
