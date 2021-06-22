<?php
namespace Test\Lucinda\Project\EventListeners;

use Lucinda\Project\EventListeners\Logging;
use Lucinda\Project\Attributes;
use Lucinda\STDOUT\Application;
use Lucinda\UnitTest\Result;

class LoggingTest
{
    public function run()
    {
        $attributes = new Attributes();
        $application = new Application(dirname(__DIR__)."/mocks/stdout.xml");

        $event = new Logging($attributes, $application);
        $event->run();

        return new Result($attributes->getLogger() ? true : false);
    }
}
