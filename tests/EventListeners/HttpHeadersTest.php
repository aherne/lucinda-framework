<?php

namespace Test\Lucinda\Project\EventListeners;

use Lucinda\Project\Attributes;
use Lucinda\STDOUT\Application;
use Lucinda\Project\EventListeners\HttpHeaders;
use Lucinda\STDOUT\Request;
use Lucinda\STDOUT\Session;
use Lucinda\STDOUT\Cookies;
use Lucinda\UnitTest\Result;

class HttpHeadersTest
{
    public function run()
    {
        $_SERVER = json_decode(file_get_contents(dirname(__DIR__)."/mocks/SERVER.json"), true);

        $attributes = new Attributes();
        $attributes->setValidPage("index");
        $application = new Application(dirname(__DIR__)."/mocks/stdout.xml");
        $request = new Request();
        $session = new Session();
        $cookies = new Cookies();

        $event = new HttpHeaders($attributes, $application, $request, $session, $cookies);
        $event->run();

        return new Result($attributes->getHeaders()->getRequest()->getHost()=="www.test.local");
    }
}
