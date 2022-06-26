<?php

namespace Test\Lucinda\Project\ErrorReporters;

use Lucinda\Logging\RequestInformation;
use Lucinda\STDERR\Application;
use Lucinda\STDERR\Request;
use Lucinda\UnitTest\Result;

class FileTest
{
    public function getLogger()
    {
        $exception = new \Exception("Hello!");
        $application = new Application(dirname(__DIR__)."/mocks/stderr.xml", ENVIRONMENT);
        $request = new Request($application->routes()[$application->getDefaultRoute()], $exception);

        $requestInformation = new RequestInformation();
        $requestInformation->setIpAddress("127.0.0.1");
        $requestInformation->setUserAgent("chrome");
        $requestInformation->setUri("/test");

        $object = new \Lucinda\Project\ErrorReporters\File($request, $application->reporters("Lucinda\Project\ErrorReporters\File"));
        return new Result($object->getLogger() instanceof \Lucinda\Logging\Driver\File\Logger);
    }
}
