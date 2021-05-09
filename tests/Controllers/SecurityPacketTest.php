<?php
namespace Test\Lucinda\Project\Controllers;

use Lucinda\STDERR\Application;
use Lucinda\STDERR\Request;
use Lucinda\MVC\Response;
use Lucinda\UnitTest\Result;
use Lucinda\Project\Controllers\SecurityPacket;
use Lucinda\WebSecurity\Authorization\ResultStatus;

class SecurityPacketTest
{
    public function run()
    {
        $exception = new \Lucinda\WebSecurity\SecurityPacket();
        $exception->setCallback("login");
        $exception->setStatus(ResultStatus::NOT_FOUND);
        
        $application = new Application(dirname(__DIR__)."/mocks/stderr.xml", ENVIRONMENT);
        $request = new Request($application->routes()['Lucinda\WebSecurity\SecurityPacket'], $exception);
        $response = new Response($application->resolvers()[$application->getDefaultFormat()]->getContentType(), "");
        
        $controller = new SecurityPacket($application, $request, $response);
        $controller->run();
        
        return new Result($response->view()->getFile() == "tests/mocks/views/404");
    }
}
