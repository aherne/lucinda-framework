<?php

namespace Test\Lucinda\Project\DAO;

use Lucinda\NoSQL\Wrapper;
use Lucinda\Project\DAO\NoSQLSessionHandler;
use Lucinda\UnitTest\Result;
use Lucinda\STDOUT\Application;

class NoSQLSessionHandlerTest extends AbstractSessionHandlerTest
{
    public function __construct()
    {
        $application = new Application(dirname(__DIR__)."/mocks/stdout.xml");
        new Wrapper($application->getXML(), ENVIRONMENT);
        $this->object = new NoSQLSessionHandler();
    }
}
