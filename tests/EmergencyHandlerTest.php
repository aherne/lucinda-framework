<?php

namespace Test\Lucinda\Project;

use Lucinda\Project\EmergencyHandler;
use Lucinda\UnitTest\Result;

class EmergencyHandlerTest
{
    public function handle()
    {
        $emergencyHandler = new EmergencyHandler();
        ob_start();
        $emergencyHandler->handle(new \Exception("Hello!"));
        $contents = ob_get_contents();
        ob_end_clean();
        return new Result(strpos($contents, "Hello!")!==false);
    }
}
