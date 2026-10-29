<?php

namespace Lucinda\Project\EventListeners;

use Lucinda\Framework\ServiceRegistry;
use Lucinda\Framework\SqlConnectionProvider;
use Lucinda\Framework\XmlDetector;
use Lucinda\MVC\EventListener\UnFaceted;

require_once dirname(__DIR__, 2)."/helpers/SQL.php";

/**
 * Sets up SQL Data Access API in order to be able to query SQL databases later on
 */
class SQLDataSource implements UnFaceted
{
    const XML_PATH = "xml/stdout/sql";
    
    public function run(): void
    {
        $xmlDetector = new XmlDetector(dirname(__DIR__, 2).DIRECTORY_SEPARATOR.self::XML_PATH);
        $provider = new SqlConnectionProvider(
            $xmlDetector->getXML(),
            ENVIRONMENT
        );
        ServiceRegistry::set(SqlConnectionProvider::class, $provider);
    }
}
