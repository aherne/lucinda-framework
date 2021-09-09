<?php
namespace Test\Lucinda\Project\EventListeners\Console;

use Lucinda\STDERR\FrontController;
use Lucinda\Project\EmergencyHandler;
use Lucinda\Project\EventListeners\Console\Error;
use Lucinda\ConsoleSTDOUT\Application;
use Lucinda\ConsoleSTDOUT\Attributes;
use Lucinda\ConsoleSTDOUT\Request;
use Lucinda\UnitTest\Result;

class ErrorTest
{
    public function run()
    {
        $frontController = new FrontController(dirname(__DIR__, 2)."/mocks/stderr.xml", ENVIRONMENT, dirname(__DIR__, 2), new EmergencyHandler());
        
        $attributes = new Attributes();
        $attributes->setValidFormat("console");
        $application = new Application(dirname(__DIR__, 2)."/mocks/stdout.xml");
        
        $event = new Error($attributes, $application);
        $event->run();
        
        ob_start();
        $frontController->handle(new \Exception("Hello!"));
        $body = ob_get_contents();
        ob_end_clean();
        
        return new Result(strpos($body, "Hello!")!==false);
    }
}
