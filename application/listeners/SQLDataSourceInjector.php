<?php
require("application/models/SQL.php");
require_once("application/models/getParentNode.php");

/**
 * Sets up SQL Data Access API in order to be able to query SQL databases later on
 */
class SQLDataSourceInjector extends \Lucinda\STDOUT\EventListeners\Application
{
    /**
     * {@inheritDoc}
     * @see \Lucinda\MVC\Runnable::run()
     */
    public function run(): void
    {
        new Lucinda\SQL\Wrapper(getParentNode($this->application, "sql"), ENVIRONMENT);
    }
}
