<?php

namespace Lucinda\Project\EventListeners;

use Lucinda\NoSQL\ConfigurationException;
use Lucinda\STDOUT\EventListeners\Application;
use Lucinda\NoSQL\Wrapper;

require_once dirname(__DIR__, 2)."/helpers/NoSQL.php";

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
