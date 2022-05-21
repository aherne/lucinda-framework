<?php

namespace Test\Lucinda\Project\EventListeners;

use Lucinda\STDERR\FrontController;
use Lucinda\Project\EmergencyHandler;
use Lucinda\Project\EventListeners\Error;
use Lucinda\STDOUT\Application;
use Lucinda\STDOUT\Attributes;
use Lucinda\STDOUT\Request;
use Lucinda\STDOUT\Session;
use Lucinda\STDOUT\Cookies;
use Lucinda\UnitTest\Result;

class ErrorTest
{
    public function run()
    {
        $frontController = new FrontController(dirname(__DIR__)."/mocks/stderr.xml", ENVIRONMENT, dirname(__DIR__, 2), new EmergencyHandler());


        $_SERVER = json_decode(file_get_contents(dirname(__DIR__)."/mocks/SERVER.json"), true);

        $attributes = new Attributes();
        $attributes->setValidFormat("json");
        $application = new Application(dirname(__DIR__)."/mocks/stdout.xml");
        $request = new Request();
        $session = new Session();
        $cookies = new Cookies();

        $event = new Error($attributes, $application, $request, $session, $cookies);
        $event->run();

        ob_start();
        $frontController->handle(new \Exception("Hello!"));
        $body = ob_get_contents();
        ob_end_clean();

        if ($val = json_decode($body, true)) {
            return new Result(!empty($val["body"]["message"]) && $val["body"]["message"]=="Hello!");
        } else {
            return new Result(false);
        }
    }
}
