<?php
namespace Test\Lucinda\Project;

use Lucinda\Project\EmergencyHandler;
use Lucinda\Project\TemplatingWrapper;
use Lucinda\STDERR\PHPException;
use Lucinda\UnitTest\Result;

class TemplatingWrapperTest
{

    public function handle()
    {
        PHPException::setErrorHandler(new EmergencyHandler());

        $wrapper = new TemplatingWrapper(simplexml_load_file(__DIR__."/mocks/stdout.xml"));
        try {
            return new Result($wrapper->compile("test-bugged", [])== "
<div>Hello, lucian!</div>

");
        } catch (\Throwable $e) {
            return new Result($e->getMessage() == "User tag not found: foo/bar");
        }
    }
        

}
