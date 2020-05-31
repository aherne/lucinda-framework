<?php
/**
 * Sets up NoSQL Data Access API in order to be able to query NoSQL key-value stores (eg: Redis) later on
 */
class NoSQLDataSourceInjector extends \Lucinda\STDOUT\EventListeners\Application
{
    /**
     * {@inheritDoc}
     * @see \Lucinda\STDOUT\Runnable::run()
     */
    public function run(): void
    {
        new Lucinda\NoSQL\Wrapper($this->application->getXML(), ENVIRONMENT);
    }
}
