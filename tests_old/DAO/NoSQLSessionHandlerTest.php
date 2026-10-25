<?php

namespace Test\Lucinda\Project\DAO;

use Lucinda\Project\DAO\NoSQLSessionHandler;
use Lucinda\Framework\ServiceRegistry;
use Lucinda\Framework\NoSqlDriverProvider;
use Lucinda\UnitTest\Result;
use Lucinda\STDOUT\Application;

class NoSQLSessionHandlerTest extends AbstractSessionHandlerTest
{
    public function __construct()
    {
        $application = new Application(dirname(__DIR__)."/mocks/stdout.xml");
        $provider = new NoSqlDriverProvider($application->getXML(), ENVIRONMENT);
        ServiceRegistry::set(NoSqlDriverProvider::class, $provider);
        $this->object = new NoSQLSessionHandler();
    }
}
