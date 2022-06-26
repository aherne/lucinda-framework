<?php

namespace Test\Lucinda\Project\EventListeners;

use Lucinda\Project\EventListeners\HttpHeaders;
use Lucinda\Project\EventListeners\HttpCaching;
use Lucinda\Project\Attributes;
use Lucinda\STDOUT\Application;
use Lucinda\STDOUT\Request;
use Lucinda\STDOUT\Session;
use Lucinda\STDOUT\Cookies;
use Lucinda\UnitTest\Result;

class HttpCachingTest
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

        $response = new \Lucinda\MVC\Response("application/json", "");
        $response->setBody(json_encode(["hello"=>"world"]));

        $event = new HttpCaching($attributes, $application, $request, $session, $cookies, $response);
        $event->run();

        return new Result($response->headers("Cache-Control")=="public, max-age=10" && $response->headers("ETag"));
    }
}
