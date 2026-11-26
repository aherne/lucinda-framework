<?php

namespace Lucinda\Project\EventListeners;

use Lucinda\DB\Wrapper;
use Lucinda\Framework\XmlDetector;
use Lucinda\MVC\EventListener\Faceted;
use Lucinda\Project\Facets\FileDB as FileDBFacet;
use Lucinda\MVC\Facet;

/**
 * Loads file database configuration and publishes its wrapper facet
 *
 * Explicit XML is retained until run(); otherwise the listener loads the
 * project configuration file. Each run creates a wrapper shared through a new
 * facet.
 */
final class FileDB implements Faceted
{
    /** Project-relative configuration path without its XML extension */
    public const XML_PATH = 'xml/stdout/file_db';

    /**
     * Explicit configuration supplied by application setup, when applicable
     */
    private ?\SimpleXMLElement $xml;

    /**
     * Captures optional configuration or defers file loading until run
     *
     * @param \SimpleXMLElement|null $xml Application XML with file database
     *     settings
     */
    public function __construct(?\SimpleXMLElement $xml = null)
    {
        $this->xml = $xml;
    }

    /**
     * Builds the file database wrapper and publishes its request facet
     *
     * @throws \Throwable If configuration mapping or registration fails
     * @return FileDBFacet Facet sharing the configured file database wrapper
     */
    public function run(): Facet
    {
        $xml = $this->xml;
        if ($xml === null) {
            $path = dirname(__DIR__, 2) . '/' . self::XML_PATH;
            $detector = new XmlDetector($path);
            $xml = $detector->getXML();
        }

        $wrapper = new Wrapper($xml);
        $facet = new FileDBFacet();
        $facet->setWrapper($wrapper);
        return $facet;
    }
}
