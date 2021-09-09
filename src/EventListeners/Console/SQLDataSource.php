<?php
namespace Lucinda\Project\EventListeners\Console;

use Lucinda\ConsoleSTDOUT\EventListeners\Application;
use Lucinda\SQL\Wrapper;

require_once(dirname(__DIR__, 3)."/helpers/SQL.php");
require_once(dirname(__DIR__, 3)."/helpers/getParentNode.php");

/**
 * Sets up SQL Data Access API in order to be able to query SQL databases later on
 */
class SQLDataSource extends Application
{
    /**
     * {@inheritDoc}
     * @see \Lucinda\MVC\Runnable::run()
     */
    public function run(): void
    {
        new Wrapper(\getParentNode($this->application, "sql"), ENVIRONMENT);
    }
}
