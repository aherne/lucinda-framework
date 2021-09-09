<?php
namespace Lucinda\Project\EventListeners\Console;

use Lucinda\ConsoleSTDOUT\EventListeners\Application;
use Lucinda\NoSQL\Wrapper;

require_once(dirname(__DIR__, 3)."/helpers/getParentNode.php");

/**
 * Sets up NoSQL Data Access API in order to be able to query NoSQL key-value stores (eg: Redis) later on
 */
class NoSQLDataSource extends Application
{
    /**
     * {@inheritDoc}
     * @see \Lucinda\MVC\Runnable::run()
     */
    public function run(): void
    {
        new Wrapper(\getParentNode($this->application, "nosql"), ENVIRONMENT);
    }
}
