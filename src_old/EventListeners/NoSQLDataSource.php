<?php

namespace Lucinda\Project\EventListeners;

use Lucinda\Framework\ServiceRegistry;
use Lucinda\Framework\NoSqlDriverProvider;
use Lucinda\NoSQL\ConfigurationException;
use Lucinda\STDOUT\EventListeners\Application;

require_once dirname(__DIR__, 2)."/helpers/NoSQL.php";

/**
 * Sets up NoSQL Data Access API in order to be able to query NoSQL key-value stores (eg: Redis) later on
 */
class NoSQLDataSource extends Application
{
    /**
     * {@inheritDoc}
     *
     * @throws ConfigurationException
     * @throws \Lucinda\MVC\ConfigurationException
     * @see    \Lucinda\MVC\Runnable::run()
     */
    public function run(): void
    {
        $provider = new NoSqlDriverProvider(
            $this->application->getTag("nosql")->xpath("..")[0],
            ENVIRONMENT
        );
        ServiceRegistry::set(NoSqlDriverProvider::class, $provider);
    }
}
