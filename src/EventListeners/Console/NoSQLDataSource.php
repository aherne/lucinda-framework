<?php

namespace Lucinda\Project\EventListeners\Console;

use Lucinda\ConsoleSTDOUT\EventListeners\Application;
use Lucinda\NoSQL\ConfigurationException;
use Lucinda\NoSQL\Wrapper;

require_once dirname(__DIR__, 3)."/helpers/NoSQL.php";

/**
 * Sets up NoSQL Data Access API in order to be able to query NoSQL key-value stores (eg: Redis) later on
 */
class NoSQLDataSource extends Application
{
    /**
     * {@inheritDoc}
     * @throws ConfigurationException
     * @throws \Lucinda\MVC\ConfigurationException
     * @see \Lucinda\MVC\Runnable::run()
     */
    public function run(): void
    {
        new Wrapper($this->application->getXML(), ENVIRONMENT);
    }
}
