<?php
require_once("application/models/getParentNode.php");

/**
 * Sets up NoSQL Data Access API in order to be able to query NoSQL key-value stores (eg: Redis) later on
 */
class NoSQLDataSourceInjector extends \Lucinda\STDOUT\EventListeners\Application
{
    /**
     * {@inheritDoc}
     * @see \Lucinda\MVC\Runnable::run()
     */
    public function run(): void
    {
        new Lucinda\NoSQL\Wrapper(getParentNode($this->application, "nosql"), ENVIRONMENT);
    }
}
