<?php

namespace Lucinda\Project\EventListeners\Console;

use Lucinda\ConsoleSTDOUT\EventListeners\Application;
use Lucinda\SQL\Wrapper;

require_once dirname(__DIR__, 3)."/helpers/SQL.php";

/**
 * Sets up SQL Data Access API in order to be able to query SQL databases later on
 */
class SQLDataSource extends Application
{
    /**
     * {@inheritDoc}
     * @throws \Lucinda\SQL\ConfigurationException
     * @throws \Lucinda\MVC\ConfigurationException
     * @see \Lucinda\MVC\Runnable::run()
     */
    public function run(): void
    {
        new Wrapper($this->application->getXML(), ENVIRONMENT);
    }
}
