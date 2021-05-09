<?php
namespace Test\Lucinda\Project\ViewResolvers;

use Lucinda\Project\ViewResolvers\Json;
use Lucinda\STDOUT\Application;
use Lucinda\MVC\Response;
use Lucinda\UnitTest\Result;

class JsonTest
{
    public function run()
    {
        $application = new Application(dirname(__DIR__)."/mocks/stdout.xml");
        $response = new Response("application/json", "");
        $response->view()["hello"] = "world";
        
        $json = new Json($application, $response);
        $json->run();
        
        return new Result($response->getBody()=='{"status":"ok","body":{"hello":"world"}}');
    }
}
