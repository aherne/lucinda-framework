<?php
namespace Test\Lucinda\Project\DAO;

use Lucinda\UnitTest\Result;
use Lucinda\WebSecurity\Request;
use Lucinda\WebSecurity\Authentication\Form\LoginThrottler;

abstract class AbstractLoginThrottlerTest
{
    public const USER_NAME = "test";
    public const USER_IP = "192.168.1.20";

    public function __construct()
    {
        $this->cleanup();
    }

    abstract protected function cleanup();

    abstract protected function getThrottlerClass();

    private function getInstance(): LoginThrottler
    {
        $request = new Request();
        $request->setIpAddress(self::USER_IP);

        $className = $this->getThrottlerClass();
        return new $className($request, self::USER_NAME);
    }

    public function setSuccess()
    {
        $throttler = $this->getInstance();
        $throttler->setSuccess();
        return new Result($throttler->getTimePenalty()==0);
    }

    public function setFailure()
    {
        $throttler = $this->getInstance();
        $throttler->setFailure();
        return new Result($throttler->getTimePenalty()==0);
    }

    public function getTimePenalty()
    {
        $throttler = null;
        for ($i=0; $i<5; $i++) {
            $throttler = $this->getInstance();
            $throttler->setFailure();
        }
        return new Result($throttler->getTimePenalty()==32);
    }
}
