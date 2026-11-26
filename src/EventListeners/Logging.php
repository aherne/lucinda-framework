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
 * Initializes the HTTP request logger and publishes its shared facet
 *
 * Each run loads logger configuration and captures the request URI, client IP
 * and optional user agent. Consumers decide which messages to log; this
 * listener initializes their logger without emitting an application message.
 */
final class Logging implements Faceted
{
    /** Project-relative configuration path without its XML extension */
    public const XML_PATH = "xml/stdout/loggers";

    /**
     * Request retained for listener execution and metadata extraction
     */
    private Request $request;

    /**
     * Captures the HTTP request whose metadata accompanies log entries
     *
     * @param Request $request Current request and client information
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * Loads logger configuration and publishes the request logger
     *
     * @return LoggersFacet Facet sharing the initialized logger
     * @throws \Throwable If configuration loading or logger setup fails
     */
    public function run(): Facet
    {

        $detector = new XmlDetector(
            dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . self::XML_PATH
        );
        $wrapper = new Wrapper(
            $detector->getXML(),
            $this->getRequestInformation()
        );
        $facet = new LoggersFacet();
        $facet->setLogger($wrapper->getLogger());
        return $facet;
    }

    /**
     * Collects the request URI, user agent and client IP for log entries
     *
     * @return RequestInformation Metadata attached to the request logger
     */
    private function getRequestInformation(): RequestInformation
    {
        $requestInformation = new RequestInformation();
        $requestInformation->setUri($this->request->getURI()->getPage());
        $requestInformation->setUserAgent(
            $this->request->headers("User-Agent") ?? "?"
        );
        $requestInformation->setIpAddress($this->request->getClient()->getIP());
        return $requestInformation;
    }
}
