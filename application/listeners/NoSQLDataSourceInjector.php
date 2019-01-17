<?php
require_once("vendor/lucinda/framework-engine/src/datasource_detection/NoSQLDataSourceBinder.php");

/**
 * Binds STDOUT MVC with NoSQL Data Access API and contents of 'nosql' subtag of 'servers' tag @ configuration.xml
 * in order to be able to operate with a nosql database (supported: memcache(d), apc(u), redis, couchbase).
 * Sets up and injects a Lucinda\NoSQL\DataSource object that will be used automatically when querying database via
 * Lucinda\NoSQL\ConnectionSingleton or Lucinda\NoSQL\ConnectionFactory.
 */
class NoSQLDataSourceInjector extends \Lucinda\MVC\STDOUT\ApplicationListener {
    /**
     * {@inheritDoc}
     * @see \Lucinda\MVC\STDOUT\Runnable::run()
     */
    public function run() {
        new Lucinda\Framework\NoSQLDataSourceBinder($this->application->getTag("servers")->nosql, ENVIRONMENT);
    }
}