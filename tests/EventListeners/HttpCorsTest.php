<?php
namespace Test\Lucinda\Project\EventListeners;

use Lucinda\Project\EventListeners\HttpHeaders;
use Lucinda\Project\EventListeners\HttpCors;
use Lucinda\Project\Attributes;
use Lucinda\STDOUT\Application;
use Lucinda\STDOUT\Request;
use Lucinda\STDOUT\Session;
use Lucinda\STDOUT\Cookies;
use Lucinda\UnitTest\Result;
use Lucinda\Headers\Response;

class HttpCorsTest
{
    public function run()
    {
        $_SERVER = [
            'HTTP_HOST' => 'www.test.local',
            'HTTP_USER_AGENT' => 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:74.0) Gecko/20100101 Firefox/74.0',
            'HTTP_ACCEPT' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
            'HTTP_ACCEPT_LANGUAGE' => 'en-US,en;q=0.5',
            'HTTP_ACCEPT_ENCODING' => 'gzip, deflate',
            'HTTP_ORIGIN' => 'https://www.translatoruser-int.com',
            'HTTP_ACCESS_CONTROL_REQUEST_HEADERS' => 'x-requested-with',
            'HTTP_ACCESS_CONTROL_REQUEST_METHOD' => 'POST',
            'SERVER_ADMIN' => '',
            'SERVER_SOFTWARE' => 'Apache/2.4.29 (Ubuntu)',
            'SERVER_NAME' => 'www.example.com',
            'SERVER_ADDR' => '127.0.0.1',
            'SERVER_PORT' => '80',
            'REMOTE_ADDR' => '127.0.0.1',
            'REMOTE_PORT' => '59300',
            'REQUEST_SCHEME' => 'http',
            'REQUEST_URI' => '/user/lucian',
            'REQUEST_METHOD' => 'OPTIONS',
            'DOCUMENT_ROOT' => '/var/www/html/documentation',
            'SCRIPT_FILENAME' => '/var/www/html/documentation/index.php',
            'QUERY_STRING' =>'asd=fgh'
        ];

        $attributes = new Attributes();
        $attributes->setValidPage("index");
        $application = new Application(dirname(__DIR__)."/mocks/stdout.xml");
        $request = new Request();
        $session = new Session();
        $cookies = new Cookies();

        $event = new HttpHeaders($attributes, $application, $request, $session, $cookies);
        $event->run();

        $headersToSend = $this->testWithoutExit($attributes, $request);
        return new Result(
            isset($headersToSend["Access-Control-Allow-Origin"]) && $headersToSend["Access-Control-Allow-Origin"]=="http://www.example.com",
            "HttpCors exits on success, so not testable directly"
        );
    }

    private function testWithoutExit(Attributes $attributes, Request $request)
    {
        // tests headers sent by HttpCors
        $validator = $attributes->getHeaders();
        if ($validator===null || $request->getMethod()!="OPTIONS") {
            return;
        }

        // perform CORS validation
        $validator->validateCors($request->getProtocol()."://".$request->getServer()->getName());
        return $validator->getResponse()->toArray();
    }
}
