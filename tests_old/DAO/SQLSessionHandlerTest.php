<?php

namespace Test\Lucinda\Project\DAO;

use Lucinda\Framework\ServiceRegistry;
use Lucinda\Framework\NoSqlDriverProvider;
use Lucinda\STDOUT\Application;
use Lucinda\Project\DAO\SQLSessionHandler;

class SQLSessionHandlerTest extends AbstractSessionHandlerTest
{
    public function __construct()
    {
        $application = new Application(dirname(__DIR__)."/mocks/stdout.xml");
        $provider = new NoSqlDriverProvider($application->getXML(), ENVIRONMENT);
        ServiceRegistry::set(NoSqlDriverProvider::class, $provider);
        $this->object = new SQLSessionHandler();
    }
}
