<?php

namespace Test\Lucinda\Project\EventListeners\Console;

use Lucinda\ConsoleSTDOUT\Request;
use Lucinda\Project\EventListeners\Console\Logging;
use Lucinda\Project\ConsoleAttributes;
use Lucinda\ConsoleSTDOUT\Application;
use Lucinda\UnitTest\Result;

class LoggingTest
{
    public function run()
    {
        $attributes = new ConsoleAttributes();
        $application = new Application(dirname(__DIR__, 2)."/mocks/stdout.xml");
        $_SERVER["argv"] = ["test", "me"];
        $event = new Logging($attributes, $application, new Request());
        $event->run();

        return new Result((bool)$attributes->getLogger());
    }
}
