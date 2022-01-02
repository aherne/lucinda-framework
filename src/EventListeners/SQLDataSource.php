<?php
namespace Lucinda\Project\EventListeners;

use Lucinda\SQL\ConfigurationException;
use Lucinda\STDOUT\EventListeners\Application;
use Lucinda\SQL\Wrapper;

require_once(dirname(__DIR__, 2)."/helpers/SQL.php");
require_once(dirname(__DIR__, 2)."/helpers/getParentNode.php");

/**
 * Sets up SQL Data Access API in order to be able to query SQL databases later on
 */
class SQLDataSource extends Application
{
    /**
     * {@inheritDoc}
     * @throws ConfigurationException
     * @throws \Lucinda\MVC\ConfigurationException
     * @see \Lucinda\MVC\Runnable::run()
     */
    public function run(): void
    {
        new Wrapper(\getParentNode($this->application, "sql"), ENVIRONMENT);
    }
}
