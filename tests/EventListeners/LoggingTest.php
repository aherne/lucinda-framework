<?php

namespace Test\Lucinda\Project\EventListeners;

use Lucinda\Project\EventListeners\Logging;
use Lucinda\Project\Attributes;
use Lucinda\STDOUT\Application;
use Lucinda\STDOUT\Cookies;
use Lucinda\STDOUT\Request;
use Lucinda\STDOUT\Session;
use Lucinda\UnitTest\Result;

class LoggingTest
{
    public function run()
    {
        $_SERVER = json_decode(file_get_contents(dirname(__DIR__)."/mocks/SERVER.json"), true);

        $attributes = new Attributes();
        $application = new Application(dirname(__DIR__)."/mocks/stdout.xml");

        $event = new Logging($attributes, $application, new Request(), new Session(), new Cookies());
        $event->run();

        return new Result((bool)$attributes->getLogger());
    }
}
