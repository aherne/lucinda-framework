<?php

namespace Test\Lucinda\Project\ViewResolvers;

use Lucinda\STDOUT\Application;
use Lucinda\MVC\Response;
use Lucinda\Project\ViewResolvers\Html;
use Lucinda\STDERR\PHPException;
use Lucinda\Project\EmergencyHandler;
use Lucinda\UnitTest\Result;

class HtmlTest
{
    public function __construct()
    {
        PHPException::setErrorHandler(new EmergencyHandler());
    }

    public function run()
    {
        $application = new Application(dirname(__DIR__)."/mocks/stdout.xml");
        $response = new Response("text/html", "test");
        $response->view()["test"] = "world";

        $html = new Html($application, $response);
        $html->run();
        return new Result($response->getBody()=="
<div>Hello, world!</div>

");
    }


    public function handle()
    {
        $application = new Application(dirname(__DIR__)."/mocks/stdout.xml");
        $response = new Response("text/html", "test-bugged");

        try {
            $html = new Html($application, $response);
            $html->run();
        } catch(\Throwable $e) {
            return new Result($e->getMessage() == "User tag not found: foo/bar");
        }
    }
}
