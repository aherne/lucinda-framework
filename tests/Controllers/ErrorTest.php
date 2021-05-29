<?php
namespace Test\Lucinda\Project\Controllers;

use Lucinda\Project\Controllers\Error;
use Lucinda\STDERR\Application;
use Lucinda\STDERR\Request;
use Lucinda\MVC\Response;
use Lucinda\UnitTest\Result;

class ErrorTest
{
    public function run()
    {
        $exception = new \Exception("Hello!");
        $application = new Application(dirname(__DIR__)."/mocks/stderr.xml", ENVIRONMENT);
        $request = new Request($application->routes()[$application->getDefaultRoute()], $exception);
        $response = new Response($application->resolvers()[$application->getDefaultFormat()]->getContentType(), "");
        
        $controller = new Error($application, $request, $response);
        $controller->run();
        
        return new Result($response->view()["message"]=="Hello!" && $response->view()->getFile()=="tests/mocks/views/debug");
    }
}
