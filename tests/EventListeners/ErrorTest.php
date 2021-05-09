<?php
namespace Test\Lucinda\Project\EventListeners;

use Lucinda\STDERR\FrontController;
use Lucinda\Project\EmergencyHandler;
use Lucinda\Project\EventListeners\Error;

class ErrorTest
{
    public function run()
    {
        new FrontController(dirname(__DIR__)."/mocks/stderr.xml", ENVIRONMENT, dirname(__DIR__, 2), new EmergencyHandler());
    }
}
