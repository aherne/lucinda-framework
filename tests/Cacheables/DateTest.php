<?php

namespace Test\Lucinda\Project\Cacheables;

use Lucinda\Project\Cacheables\Date;
use Lucinda\NoSQL\Wrapper;
use Lucinda\STDOUT\Application;
use Lucinda\UnitTest\Result;

require_once dirname(__DIR__, 2)."/helpers/NoSQL.php";

class DateTest
{
    public function getTime()
    {
        $_SERVER = json_decode(file_get_contents(dirname(__DIR__)."/mocks/SERVER.json"), true);

        $application = new Application(dirname(__DIR__)."/mocks/stdout.xml");
        new Wrapper($application->getXML(), ENVIRONMENT);

        $request = new \Lucinda\STDOUT\Request();
        $response = new \Lucinda\MVC\Response("text/plain", "");
        $response->setBody("asdfg");

        $cacheable = new Date($request, $response);
        $try1 = $cacheable->getTime();

        $cacheable = new Date($request, $response);
        $try2 = $cacheable->getTime();

        return new Result($try1 && ($try1 == $try2));
    }
}
