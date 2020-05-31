<?php
require("application/models/SQL.php");

/**
 * Sets up SQL Data Access API in order to be able to query SQL databases later on
 */
class SQLDataSourceInjector extends \Lucinda\STDOUT\EventListeners\Application
{
    /**
     * {@inheritDoc}
     * @see \Lucinda\STDOUT\Runnable::run()
     */
    public function run(): void
    {
        new Lucinda\SQL\Wrapper($this->application->getXML(), ENVIRONMENT);
    }
}
