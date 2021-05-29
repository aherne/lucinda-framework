<?php
namespace Test\Lucinda\Project\Loggers;

use Lucinda\Project\Loggers\SysLog;
use Lucinda\STDOUT\Application;
use Lucinda\UnitTest\Result;
use Lucinda\Logging\Logger;

class SysLogTest
{
    public function getLogger()
    {
        $application = new Application(dirname(__DIR__)."/mocks/stdout.xml");
        $loggers = (array) $application->getTag("loggers")->{ENVIRONMENT};
        
        $object = new SysLog($loggers["logger"][1]);
        return new Result($object->getLogger() instanceof Logger);
    }
}
