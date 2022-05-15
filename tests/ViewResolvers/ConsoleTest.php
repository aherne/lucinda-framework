<?php

namespace Test\Lucinda\Project\ViewResolvers;

use Lucinda\Project\ViewResolvers\Console;
use Lucinda\MVC\Response;
use Lucinda\ConsoleSTDOUT\Application;
use Lucinda\STDERR\PHPException;
use Lucinda\UnitTest\Result;
use Lucinda\Project\EmergencyHandler;

class ConsoleTest
{
    public function __construct()
    {
        PHPException::setErrorHandler(new EmergencyHandler());
    }

    public function run()
    {
        $application = new Application(dirname(__DIR__)."/mocks/stdout.xml");
        $response = new Response("text/plain", "test");
        $response->view()["test"] = "world";

        $html = new Console($application, $response);
        $html->run();

        return new Result($response->getBody()=="Hello, world!");
    }


    public function handle()
    {
        $application = new Application(dirname(__DIR__)."/mocks/stdout.xml");
        $response = new Response("text/plain", "test-bugged");

        ob_start();
        $html = new Console($application, $response);
        $html->run();
        $body = ob_get_contents();
        ob_end_clean();

        return new Result(strpos($body, "User tag not found: foo/bar"));
    }
}
