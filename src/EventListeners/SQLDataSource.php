<?php

namespace Lucinda\Project\EventListeners;

use Lucinda\Framework\ServiceRegistry;
use Lucinda\Framework\SqlConnectionProvider;
use Lucinda\SQL\ConfigurationException;
use Lucinda\STDOUT\EventListeners\Application;

require_once dirname(__DIR__, 2)."/helpers/SQL.php";

/**
 * Sets up SQL Data Access API in order to be able to query SQL databases later on
 */
class SQLDataSource extends Application
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
        $provider = new SqlConnectionProvider(
            $this->application->getTag("sql")->xpath("..")[0],
            ENVIRONMENT
        );
        ServiceRegistry::set(SqlConnectionProvider::class, $provider);
    }
}
