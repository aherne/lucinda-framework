<?php
namespace Test\Lucinda\Project\Loggers;

use Lucinda\Project\Loggers\File;
use Lucinda\STDOUT\Application;
use Lucinda\UnitTest\Result;
use Lucinda\Logging\Logger;

class FileTest
{
    public function getLogger()
    {
        $application = new Application(dirname(__DIR__)."/mocks/stdout.xml");
        $loggers = (array) $application->getTag("loggers")->{ENVIRONMENT};

        $object = new File($loggers["logger"][0]);
        return new Result($object->getLogger() instanceof Logger);
    }
}
