<?php

namespace Lucinda\Project\EventListeners;

use Lucinda\Framework\ServiceRegistry;
use Lucinda\Framework\NoSqlDriverProvider;
use Lucinda\Framework\XmlDetector;
use Lucinda\MVC\EventListener\UnFaceted;

/**
 * Sets up NoSQL Data Access API in order to be able to query NoSQL key-value stores (eg: Redis) later on
 */
class NoSQLDataSource implements UnFaceted
{
    const XML_PATH = "xml/stdout/nosql";
    
    public function run(): void
    {
        $xmlDetector = new XmlDetector(dirname(__DIR__, 2).DIRECTORY_SEPARATOR.self::XML_PATH);
        $provider = new NoSqlDriverProvider($xmlDetector->getXML());
        ServiceRegistry::set(NoSqlDriverProvider::class, $provider);
    }
}
