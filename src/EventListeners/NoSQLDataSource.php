<?php

namespace Lucinda\Project\EventListeners;

use Lucinda\Framework\NoSqlDriverProvider;
use Lucinda\Framework\XmlDetector;
use Lucinda\MVC\EventListener;
use Lucinda\MVC\EventListener\Faceted;
use Lucinda\MVC\Facet;
use Lucinda\Project\Cacheables\Date;

/**
 * Owns NoSQL XML mapping and publishes the application's lazy driver provider
 *
 * Application setup calls getAtomicDriver() on the returned provider and
 * injects that cached adapter into NoSqlExecutor. The provider creates the
 * adapter on first access and retains the configured native connection.
 */
final class NoSQLDataSource implements EventListener, Faceted
{
    /** Project-relative configuration path without its XML extension */
    public const XML_PATH = 'xml/stdout/nosql';

    /**
     * Explicit configuration supplied by application setup, when applicable
     */
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
     * Maps NoSQL configuration and publishes its lazy driver provider
     *
     * @throws \Throwable If configuration mapping fails
     * @return NoSqlDriverProvider Provider used to select atomic adapters
     */
    public function run(): Facet
    {
        $xml = $this->xml;
        if ($xml === null) {
            $path = dirname(__DIR__, 2) . '/' . self::XML_PATH;
            $detector = new XmlDetector($path);
            $xml = $detector->getXML();
        }

        $provider = new NoSqlDriverProvider($xml);
        Date::setDriver($provider->getAtomicDriver());
        return $provider;
    }
}
