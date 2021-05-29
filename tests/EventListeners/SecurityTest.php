<?php
namespace Test\Lucinda\Project\EventListeners;

use Lucinda\WebSecurity\Authentication\OAuth2\Exception as OAuth2Exception;
use Lucinda\Project\EventListeners\Security;
use Lucinda\UnitTest\Result;
use Lucinda\WebSecurity\SecurityPacket;
use Lucinda\STDOUT\Application;
use Lucinda\STDOUT\Request;
use Lucinda\Project\Attributes;
use Lucinda\STDOUT\Session;
use Lucinda\STDOUT\Cookies;

class SecurityTest
{
    private $application;
    private $request;
    private $cookies;
    private $session;
    private $attributes;
    
    public function __construct()
    {
        $this->attributes = new Attributes();
        $this->session = new Session();
        $this->cookies = new Cookies();
    }
    
    public function run()
    {
        $results = [];
        $combinations = ["dao_dao", "dao_xml", "xml_dao", "xml_xml"];
        foreach ($combinations as $name) {
            $results = array_merge($results, $this->testNormal($name));
        }
        
        $combinations = ["oauth2_dao", "oauth2_xml"];
        foreach ($combinations as $name) {
            $results = array_merge($results, $this->testOAuth2($name));
        }
        
        return $results;
    }
    
    private function testNormal(string $name): array
    {
        $type = strtoupper(str_replace("_", " Authentication + ", $name)." Authorization");
        $results = [];
        
        $application = new Application(dirname(__DIR__)."/mocks/stdout_".$name.".xml");
        try {
            $this->setRequest("asdf");
            $event = new Security($this->attributes, $application, $this->request, $this->session, $this->cookies);
            $event->run();
            $results[] = new Result(false, $type.": check not found page");
        } catch (SecurityPacket $packet) {
            $results[] = new Result($packet->getStatus()=="not_found", $type.": check not found page");
        }
        
        $this->setRequest("login");
        $event = new Security($this->attributes, $application, $this->request, $this->session, $this->cookies);
        $event->run();
        $results[] = new Result($this->attributes->getUserID()==null, $type.": check found page");
        $csrfToken = $this->attributes->getCsrfToken();
        
        try {
            $this->setRequest("login", "POST", ["username"=>"test", "password"=>"me1", "csrf"=>$csrfToken]);
            $event = new Security($this->attributes, $application, $this->request, $this->session, $this->cookies);
            $event->run();
            $results[] = new Result(false, $type.": check login failure");
        } catch (SecurityPacket $packet) {
            $results[] = new Result($packet->getStatus()=="login_failed", $type.": check login failure");
        }
        
        $accessToken = "";
        try {
            $this->setRequest("login", "POST", ["username"=>"test", "password"=>"me", "csrf"=>$csrfToken]);
            $event = new Security($this->attributes, $application, $this->request, $this->session, $this->cookies);
            $event->run();
            $results[] = new Result(false, $type.": check login success");
        } catch (SecurityPacket $packet) {
            $accessToken = $packet->getAccessToken();
            $results[] = new Result($packet->getStatus()=="login_ok", $type.": check login success");
        }
        
        $this->setRequest("index", "GET", [], $accessToken);
        $event = new Security($this->attributes, $application, $this->request, $this->session, $this->cookies);
        $event->run();
        $results[] = new Result($this->attributes->getUserID()==1, $type.": check logged in user access to authorized page");
        
        try {
            $this->setRequest("administration", "GET", [], $accessToken);
            $event = new Security($this->attributes, $application, $this->request, $this->session, $this->cookies);
            $event->run();
            $results[] = new Result(false, $type.": check logged in user access to forbidden page");
        } catch (SecurityPacket $packet) {
            $results[] = new Result($packet->getStatus()=="forbidden", $type.": check logged in user access to forbidden page");
        }
        
        try {
            $this->setRequest("logout", "GET", [], $accessToken);
            $event = new Security($this->attributes, $application, $this->request, $this->session, $this->cookies);
            $event->run();
            $results[] = new Result(false, $type.": check logout success");
        } catch (SecurityPacket $packet) {
            $results[] = new Result($packet->getStatus()=="logout_ok", $type.": check logout success");
        }
        
        try {
            $this->setRequest("logout");
            $event = new Security($this->attributes, $application, $this->request, $this->session, $this->cookies);
            $event->run();
            $results[] = new Result(false, $type.": check logout failure");
        } catch (SecurityPacket $packet) {
            $results[] = new Result($packet->getStatus()=="logout_failed", $type.": check logout failure");
        }
        
        try {
            $this->setRequest("index");
            $event = new Security($this->attributes, $application, $this->request, $this->session, $this->cookies);
            $event->run();
            $results[] = new Result(false, $type.": check guest user access to page requiring login");
        } catch (SecurityPacket $packet) {
            $results[] = new Result($packet->getStatus()=="unauthorized", $type.": check guest user access to page requiring login");
        }
        
        return $results;
    }
    
    private function testOAuth2(string $name): array
    {
        $type = strtoupper(str_replace("_", " Authentication + ", $name)." Authorization");
        $results = [];
        
        $application = new Application(dirname(__DIR__)."/mocks/stdout_".$name.".xml");
        try {
            $this->setRequest("asdf");
            $event = new Security($this->attributes, $application, $this->request, $this->session, $this->cookies);
            $event->run();
            $results[] = new Result(false, $type.": check not found page");
        } catch (SecurityPacket $packet) {
            $results[] = new Result($packet->getStatus()=="not_found", $type.": check not found page");
        }
        
        $this->setRequest("login");
        $event = new Security($this->attributes, $application, $this->request, $this->session, $this->cookies);
        $event->run();
        $results[] = new Result($this->attributes->getUserID()==null, $type.": check found page");
        $csrfToken = $this->attributes->getCsrfToken();
        
        try {
            $this->setRequest("login/facebook");
            $event = new Security($this->attributes, $application, $this->request, $this->session, $this->cookies);
            $event->run();
            $results[] = new Result(false, $type.": good authorization code");
        } catch (SecurityPacket $packet) {
            $results[] = new Result($packet->getStatus()=="redirect" && strpos($packet->getCallback(), "https://www.facebook.com/v2.8/dialog/oauth")===0, $type.": good authorization code");
        }
                
//         try {
//             $this->setRequest("login/facebook", "GET", ["error"=>"asdfgi"]);
//             $event = new Security($this->attributes, $application, $this->request, $this->session, $this->cookies);
//             $event->run();
//             $results[] = new Result(false, $type.": bad authorization code");
//         } catch (OAuth2Exception $e) {
//             $results[] = new Result(true, $type.": bad authorization code");
//         }
        
//         $accessToken = "";
//         try {
//             $this->setRequest("login/facebook", "GET", ["code"=>"qwerty", "state"=>$csrfToken]);
//             $event = new Security($this->attributes, $application, $this->request, $this->session, $this->cookies);
//             $event->run();
//             $results[] = new Result(false, $type.": a1ccess token");
//         } catch (SecurityPacket $packet) {
//             $accessToken = $packet->getAccessToken();
//             $results[] = new Result($packet->getStatus()=="login_ok", $type.": a2ccess token");
//         }
        
//         $this->setRequest("index", "GET", [], $accessToken);
//         $event = new Security($this->attributes, $application, $this->request, $this->session, $this->cookies);
//         $event->run();
//         $results[] = new Result($this->attributes->getUserID()==1, $type.": check logged in user access to authorized page");
        
//         try {
//             $this->setRequest("administration", "GET", [], $accessToken);
//             $event = new Security($this->attributes, $application, $this->request, $this->session, $this->cookies);
//             $event->run();
//             $results[] = new Result(false, $type.": check logged in user access to forbidden page");
//         } catch (SecurityPacket $packet) {
//             $results[] = new Result($packet->getStatus()=="forbidden", $type.": check logged in user access to forbidden page");
//         }
        
//         try {
//             $this->setRequest("logout", "GET", [], $accessToken);
//             $event = new Security($this->attributes, $application, $this->request, $this->session, $this->cookies);
//             $event->run();
//             $results[] = new Result(false, $type.": check logout success");
//         } catch (SecurityPacket $packet) {
//             $results[] = new Result($packet->getStatus()=="logout_ok", $type.": check logout success");
//         }
        
//         try {
//             $this->setRequest("logout");
//             $event = new Security($this->attributes, $application, $this->request, $this->session, $this->cookies);
//             $event->run();
//             $results[] = new Result(false, $type.": check logout failure");
//         } catch (SecurityPacket $packet) {
//             $results[] = new Result($packet->getStatus()=="logout_failed", $type.": check logout failure");
//         }
        
//         try {
//             $this->setRequest("index");
//             $event = new Security($this->attributes, $application, $this->request, $this->session, $this->cookies);
//             $event->run();
//             $results[] = new Result(false, $type.": check guest user access to page requiring login");
//         } catch (SecurityPacket $packet) {
//             $results[] = new Result($packet->getStatus()=="unauthorized", $type.": check guest user access to page requiring login");
//         }
        
        return $results;
    }
    
    private function setRequest(string $uri, string $method="GET", array $parameters=[], string $accessToken=""): void
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
            'REQUEST_URI' => "/".$uri,
            'REQUEST_METHOD' => $method,
            'DOCUMENT_ROOT' => '/var/www/html/documentation',
            'SCRIPT_FILENAME' => '/var/www/html/documentation/index.php',
            'QUERY_STRING' =>($method=="GET" && $parameters?http_build_query($parameters):"")
        ];
        if ($accessToken) {
            $_SERVER["HTTP_AUTHORIZATION"] = "Bearer ".$accessToken;
        }
        if (!empty($parameters)) {
            if ($method=="POST") {
                $_POST = $parameters;
            }
            if ($method=="GET") {
                $_GET = $parameters;
            }
        }
        $this->attributes->setValidPage($uri);
        $this->request = new Request();
    }
}
