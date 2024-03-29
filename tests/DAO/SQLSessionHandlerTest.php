<?php

namespace Test\Lucinda\Project\DAO;

use Lucinda\NoSQL\Wrapper;
use Lucinda\STDOUT\Application;
use Lucinda\Project\DAO\SQLSessionHandler;

class SQLSessionHandlerTest extends AbstractSessionHandlerTest
{
    public function __construct()
    {
        $application = new Application(dirname(__DIR__)."/mocks/stdout.xml");
        new Wrapper($application->getXML(), ENVIRONMENT);
        $this->object = new SQLSessionHandler();
    }
}
