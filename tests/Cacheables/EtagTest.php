<?php
namespace Test\Lucinda\Project\Cacheables;

use Lucinda\Project\Cacheables\Etag;
use Lucinda\NoSQL\Wrapper;
use Lucinda\STDOUT\Application;
use Lucinda\UnitTest\Result;

require_once(dirname(__DIR__, 2)."/helpers/getParentNode.php");

class EtagTest
{
    public function getEtag()
    {
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
        
        $application = new Application(dirname(__DIR__)."/mocks/stdout.xml");
        new Wrapper(\getParentNode($application, "nosql"), ENVIRONMENT);
        
        $request = new \Lucinda\STDOUT\Request();
        $response = new \Lucinda\MVC\Response("text/plain", "");
        $response->setBody("asdfg");
        
        $cacheable = new Etag($request, $response);
        $try1 = $cacheable->getEtag();
        
        $cacheable = new Etag($request, $response);
        $try2 = $cacheable->getEtag();
        
        return new Result($try1 && ($try1 == $try2));
    }
}
