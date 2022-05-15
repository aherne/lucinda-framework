<?php

namespace Test\Lucinda\Project\EventListeners\Console;

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

        $event = new Logging($attributes, $application);
        $event->run();

        return new Result($attributes->getLogger() ? true : false);
    }
}
