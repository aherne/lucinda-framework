<?php

namespace Test\Lucinda\Project\Cacheables;

use Lucinda\Project\Cacheables\Etag;
use Lucinda\STDOUT\Application;
use Lucinda\UnitTest\Result;

class EtagTest
{
    public function getEtag()
    {
        $_SERVER = json_decode(file_get_contents(dirname(__DIR__)."/mocks/SERVER.json"), true);

        $application = new Application(dirname(__DIR__)."/mocks/stdout.xml");

        $request = new \Lucinda\STDOUT\Request();
        $response = new \Lucinda\MVC\Response("text/plain", "");
        $response->setBody("asdfg");

        $cacheable = new Etag($request, $response);
        $try1 = $cacheable->getEtag();

        $cacheable = new Etag($request, $response);
        $try2 = $cacheable->getEtag();

        return new Result($try1 && ($try1 == $try2));
    }
}
