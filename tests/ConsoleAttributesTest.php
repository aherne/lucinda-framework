<?php

namespace Test\Lucinda\Project;

use Lucinda\Project\ConsoleAttributes;
use Lucinda\UnitTest\Result;

class ConsoleAttributesTest
{
    private $xml;
    private $attributes;

    public function __construct()
    {
        $this->attributes = new ConsoleAttributes();
        $this->xml = simplexml_load_file(__DIR__."/mocks/stdout.xml");
    }


    public function setLogger()
    {
        $wrapper = new \Lucinda\Logging\Wrapper($this->xml, ENVIRONMENT);
        $this->attributes->setLogger($wrapper->getLogger());
        return new Result(true, "tested via getLogger");
    }


    public function getLogger()
    {
        return new Result($this->attributes->getLogger() instanceof \Lucinda\Logging\MultiLogger);
    }
}
