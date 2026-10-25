<?php

namespace Test\Lucinda\Project\EventListeners;

use Lucinda\Project\EventListeners\Localization;
use Lucinda\Project\Attributes;
use Lucinda\STDOUT\Application;
use Lucinda\STDOUT\Request;
use Lucinda\STDOUT\Session;
use Lucinda\STDOUT\Cookies;
use Lucinda\UnitTest\Result;
use Lucinda\Internationalization\Reader;

class LocalizationTest
{
    public function run()
    {
        $_SERVER = json_decode(file_get_contents(dirname(__DIR__)."/mocks/SERVER.json"), true);

        $attributes = new Attributes();
        $application = new Application(dirname(__DIR__)."/mocks/stdout.xml");
        $request = new Request();
        $session = new Session();
        $cookies = new Cookies();

        $event = new Localization($attributes, $application, $request, $session, $cookies);
        $event->run();

        return new Result(\translate("welcome", "", "Lucian") == "Welcome to my site, Lucian!");
    }
}
