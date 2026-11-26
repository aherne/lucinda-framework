<?php

namespace Lucinda\Project\EventListeners\Console;

use Lucinda\Framework\XmlDetector;
use Lucinda\Logging\RequestInformation;
use Lucinda\MVC\EventListener\Faceted;
use Lucinda\Project\Facets\Loggers as LoggersFacet;
use Lucinda\Logging\Wrapper;
use Lucinda\MVC\Facet;
use Lucinda\ConsoleSTDOUT\Request;

/**
 * Initializes the console command logger and publishes its shared facet
 *
 * Each run captures the command route as request metadata and constructs the
 * configured logger. Consumers share that logger through the returned facet.
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
     * Captures the console command whose route accompanies log entries
     *
     * @param Request $request Current console request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * Loads logger configuration and publishes the console request logger
     *
     * @return LoggersFacet Facet sharing the initialized logger
     * @throws \Throwable If configuration loading or logger setup fails
     */
    public function run(): Facet
    {

        $detector = new XmlDetector(
            dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . self::XML_PATH
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
     * Collects the console route used to identify log entries
     *
     * @return RequestInformation Metadata identifying the current command
     */
    private function getRequestInformation(): RequestInformation
    {
        $requestInformation = new RequestInformation();
        $requestInformation->setUri($this->request->getRoute());
        return $requestInformation;
    }
}
