<?php

namespace Lucinda\Project\EventListeners;

use Lucinda\Framework\SqlConnectionProvider;
use Lucinda\Framework\XmlDetector;
use Lucinda\MVC\EventListener\Faceted;
use Lucinda\MVC\Facet;

/**
 * Owns SQL XML mapping and publishes a lazy connection provider
 *
 * Run this listener before constructing session or security flows that use
 * SQL DAOs. Application setup injects a connection from this provider into
 * its executors instead of reopening configuration.
 */
final class SQLDataSource implements Faceted
{
    /** Project-relative configuration path without its XML extension */
    public const XML_PATH = 'xml/stdout/sql';

    /**
     * Explicit configuration supplied by application setup, when applicable
     */
    private ?\SimpleXMLElement $xml;

    /**
     * Captures optional configuration or defers file loading until run
     *
     * @param \SimpleXMLElement|null $xml Application XML with a sql section
     */
    public function __construct(?\SimpleXMLElement $xml = null)
    {
        $this->xml = $xml;
    }

    /**
     * Maps SQL configuration and publishes its lazy connection owner
     *
     * @throws \Throwable If configuration mapping or registration fails
     * @return SqlConnectionProvider Lazy provider for configured SQL
     *     connections
     */
    public function run(): Facet
    {
        $xml = $this->xml;
        if ($xml === null) {
            $path = dirname(__DIR__, 2) . '/' . self::XML_PATH;
            $detector = new XmlDetector($path);
            $xml = $detector->getXML();
        }

        return new SqlConnectionProvider($xml);
    }
}
