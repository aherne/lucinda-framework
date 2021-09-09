<?php
namespace Test\Lucinda\Project\EventListeners;

use Lucinda\STDERR\FrontController;
use Lucinda\Project\EmergencyHandler;
use Lucinda\Project\EventListeners\Error;
use Lucinda\STDOUT\Application;
use Lucinda\STDOUT\Attributes;
use Lucinda\STDOUT\Request;
use Lucinda\STDOUT\Session;
use Lucinda\STDOUT\Cookies;
use Lucinda\UnitTest\Result;

class ErrorTest
{
    public function run()
    {
        $frontController = new FrontController(dirname(__DIR__)."/mocks/stderr.xml", ENVIRONMENT, dirname(__DIR__, 2), new EmergencyHandler());

        $_SERVER = [
            'HTTP_HOST' => 'www.test.local',
            'HTTP_USER_AGENT' => 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:74.0) Gecko/20100101 Firefox/74.0',
            'HTTP_ACCEPT' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
            'HTTP_ACCEPT_LANGUAGE' => 'en-US,en;q=0.5',
            'HTTP_ACCEPT_ENCODING' => 'gzip, deflate',
            'HTTP_CONNECTION' => 'keep-alive',
            'HTTP_COOKIE' => '_ga=GA1.2.1051007502.1535802299',
            'HTTP_UPGRADE_INSECURE_REQUESTS' => '1',
            'HTTP_CACHE_CONTROL' => 'max-age=0',
            'SERVER_ADMIN' => '',
            'SERVER_SOFTWARE' => 'Apache/2.4.29 (Ubuntu)',
            'SERVER_NAME' => 'www.documentation.local',
            'SERVER_ADDR' => '127.0.0.1',
            'SERVER_PORT' => '80',
            'REMOTE_ADDR' => '127.0.0.1',
            'REMOTE_PORT' => '59300',
            'REQUEST_SCHEME' => 'http',
            'REQUEST_URI' => '/user/lucian',
            'REQUEST_METHOD' => 'GET',
            'DOCUMENT_ROOT' => '/var/www/html/documentation',
            'SCRIPT_FILENAME' => '/var/www/html/documentation/index.php',
            'QUERY_STRING' =>'asd=fgh'
        ];

        $attributes = new Attributes();
        $attributes->setValidFormat("json");
        $application = new Application(dirname(__DIR__)."/mocks/stdout.xml");
        $request = new Request();
        $session = new Session();
        $cookies = new Cookies();

        $event = new Error($attributes, $application, $request, $session, $cookies);
        $event->run();

        ob_start();
        $frontController->handle(new \Exception("Hello!"));
        $body = ob_get_contents();
        ob_end_clean();

        if ($val = json_decode($body, true)) {
            return new Result(!empty($val["body"]["message"]) && $val["body"]["message"]=="Hello!");
        } else {
            return new Result(false);
        }
    }
}
