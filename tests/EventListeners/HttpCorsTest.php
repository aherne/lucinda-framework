<?php

namespace Test\Lucinda\Project\EventListeners;

use Lucinda\Project\EventListeners\HttpHeaders;
use Lucinda\Project\EventListeners\HttpCors;
use Lucinda\Project\Attributes;
use Lucinda\STDOUT\Application;
use Lucinda\STDOUT\Request;
use Lucinda\STDOUT\Session;
use Lucinda\STDOUT\Cookies;
use Lucinda\UnitTest\Result;
use Lucinda\Headers\Response;

class HttpCorsTest
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

        $headersToSend = $this->testWithoutExit($attributes, $request);
        return new Result(
            isset($headersToSend["Access-Control-Allow-Origin"]) && $headersToSend["Access-Control-Allow-Origin"]=="http://www.example.com",
            "HttpCors exits on success, so not testable directly"
        );
    }

    private function testWithoutExit(Attributes $attributes, Request $request)
    {
        // tests headers sent by HttpCors
        $validator = $attributes->getHeaders();
        if ($validator===null || $request->getMethod()!="OPTIONS") {
            return;
        }

        // perform CORS validation
        $validator->validateCors($request->getProtocol()->value."://".$request->getServer()->getName());
        return $validator->getResponse()->toArray();
    }
}
