<?php

namespace Lucinda\Project\EventListeners;

use Lucinda\Framework\NoSqlDriverProvider;
use Lucinda\Framework\XmlDetector;
use Lucinda\MVC\EventListener\Faceted;
use Lucinda\MVC\Facet;

/**
 * Owns NoSQL XML mapping and registers the application's lazy driver provider
 *
 * Executors select isolated adapters from the drivers supplied here. This
 * listener must run before NoSQL-backed session or security persistence.
 */
class NoSQLDataSource implements Faceted
{
    public const XML_PATH = 'xml/stdout/nosql';

    /** Explicit configuration supplied by application setup, when applicable */
    private ?\SimpleXMLElement $xml;

    /**
     * Captures optional configuration or defers file loading until run
     *
     * @param \SimpleXMLElement|null $xml Application XML with a nosql section
     */
    public function __construct(?\SimpleXMLElement $xml = null)
    {
        $this->xml = $xml;
    }

    /**
     * Maps NoSQL configuration and registers its connection owner
     *
     * @throws \Throwable If configuration mapping or registration fails
     * @return Facet 
     */
    public function run(): Facet
    {
        $xml = $this->xml;
        if ($xml === null) {
            $path = dirname(__DIR__, 2) . '/' . self::XML_PATH;
            $detector = new XmlDetector($path);
            $xml = $detector->getXML();
        }
        
        return new NoSqlDriverProvider($xml);
    }
}
