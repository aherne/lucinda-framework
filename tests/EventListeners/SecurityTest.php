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
            $results = array_merge($results, $this->test($name));
        }

        $combinations = ["oauth2_dao", "oauth2_xml"];
        foreach ($combinations as $name) {
            $parts = explode("_", $name);
            $error = "not testable without direct vendor connection";
            $results[] =  new Result(
                false,
                strtoupper($parts[0])." AUTHENTICATION + ".strtoupper($parts[1])." AUTHORIZATION: ".$error
            );
        }

        return $results;
    }

    private function test(string $name): array
    {
        $type = strtoupper(str_replace("_", " Authentication + ", $name)." Authorization");
        $results = [];

        // test page not found
        $application = new Application(dirname(__DIR__)."/mocks/stdout_".$name.".xml");
        try {
            $this->setRequest("asdf");
            $this->executeEvent($application);
            $results[] = new Result(false, $type.": check not found page");
        } catch (SecurityPacket $packet) {
            $results[] = new Result($packet->getStatus()=="not_found", $type.": check not found page");
        }

        // test page found and allowed access as guest
        $this->setRequest("login");
        $this->executeEvent($application);
        $results[] = new Result($this->attributes->getUserID()==null, $type.": check found page");
        $csrfToken = $this->attributes->getCsrfToken();

        // test login failed
        try {
            $this->setRequest("login", "POST", ["username"=>"test", "password"=>"me1", "csrf"=>$csrfToken]);
            $this->executeEvent($application);
            $results[] = new Result(false, $type.": check login failure");
        } catch (SecurityPacket $packet) {
            $results[] = new Result($packet->getStatus()=="login_failed", $type.": check login failure");
        }

        // test login successful
        $accessToken = "";
        try {
            $this->setRequest("login", "POST", ["username"=>"test", "password"=>"me", "csrf"=>$csrfToken]);
            $this->executeEvent($application);
            $results[] = new Result(false, $type.": check login success");
        } catch (SecurityPacket $packet) {
            $accessToken = $packet->getAccessToken();
            $results[] = new Result($packet->getStatus()=="login_ok", $type.": check login success");
        }

        // test access to member page while logged in
        $this->setRequest("index", "GET", [], $accessToken);
        $event = new Security($this->attributes, $application, $this->request, $this->session, $this->cookies);
        $event->run();
        $results[] = new Result($this->attributes->getUserID()==1, $type.": check logged in user access to authorized page");

        // test access to unauthorized member page while logged in
        try {
            $this->setRequest("administration", "GET", [], $accessToken);
            $this->executeEvent($application);
            $results[] = new Result(false, $type.": check logged in user access to forbidden page");
        } catch (SecurityPacket $packet) {
            $results[] = new Result($packet->getStatus()=="forbidden", $type.": check logged in user access to forbidden page");
        }

        // test logout while logged in
        try {
            $this->setRequest("logout", "GET", [], $accessToken);
            $this->executeEvent($application);
            $results[] = new Result(false, $type.": check logout success");
        } catch (SecurityPacket $packet) {
            $results[] = new Result($packet->getStatus()=="logout_ok", $type.": check logout success");
        }

        // test logout while not logged in
        try {
            $this->setRequest("logout");
            $this->executeEvent($application);
            $results[] = new Result(false, $type.": check logout failure");
        } catch (SecurityPacket $packet) {
            $results[] = new Result($packet->getStatus()=="logout_failed", $type.": check logout failure");
        }

        // test access to member page while not logged in
        try {
            $this->setRequest("index");
            $this->executeEvent($application);
            $results[] = new Result(false, $type.": check guest user access to page requiring login");
        } catch (SecurityPacket $packet) {
            $results[] = new Result($packet->getStatus()=="unauthorized", $type.": check guest user access to page requiring login");
        }

        return $results;
    }

    private function setRequest(string $uri, string $method = "GET", array $parameters = [], string $accessToken = ""): void
    {
        $_SERVER = json_decode(file_get_contents(dirname(__DIR__)."/mocks/SERVER.json"), true);
        $_SERVER["REQUEST_URI"] = "/".$uri;
        $_SERVER["REQUEST_METHOD"] = $method;
        $_SERVER["QUERY_STRING"] = ($method=="GET" && $parameters ? http_build_query($parameters) : "");
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

    private function executeEvent(Application $application): void
    {
        $event = new Security($this->attributes, $application, $this->request, $this->session, $this->cookies);
        $event->run();
    }
}
