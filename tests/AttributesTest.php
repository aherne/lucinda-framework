<?php
namespace Test\Lucinda\Project;

use Lucinda\Project\Attributes;
use Lucinda\UnitTest\Result;

class AttributesTest
{
    private $xml;
    private $attributes;

    public function __construct()
    {
        $this->attributes = new Attributes();
        $this->xml = simplexml_load_file(__DIR__."/mocks/stdout.xml");
    }

    public function setHeaders()
    {
        $wrapper = new \Lucinda\Headers\Wrapper($this->xml, "index", ["Accept"=>"text/html, application/xml;q=0.9, */*;q=0.8", "Accept-Charset"=>"utf-8, iso-8859-1;q=0.5"]);
        $this->attributes->setHeaders($wrapper);
        return new Result(true, "tested via getHeaders");
    }


    public function getHeaders()
    {
        return new Result($this->attributes->getHeaders() instanceof \Lucinda\Headers\Wrapper);
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


    public function setUserId()
    {
        $this->attributes->setUserId(123);
        return new Result(true, "tested via getUserId");
    }


    public function getUserId()
    {
        return new Result($this->attributes->getUserId()==123);
    }


    public function setCsrfToken()
    {
        $this->attributes->setCsrfToken("asdfghjkl");
        return new Result(true, "tested via getCsrfToken");
    }


    public function getCsrfToken()
    {
        return new Result($this->attributes->getCsrfToken()=="asdfghjkl");
    }


    public function setAccessToken()
    {
        $this->attributes->setAccessToken("qwertyuio");
        return new Result(true, "tested via getAccessToken");
    }


    public function getAccessToken()
    {
        return new Result($this->attributes->getAccessToken()=="qwertyuio");
    }
}
