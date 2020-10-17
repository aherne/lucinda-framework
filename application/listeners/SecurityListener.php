<?php
require("application/models/getRemoteResource.php");
require_once("application/models/getParentNode.php");

use Lucinda\Framework\RequestBinder;
use Lucinda\Framework\OAuth2\Binder as OAuth2Binder;
use Lucinda\Framework\OAuth2\DriverDetector as OAuth2DriverDetector;
use Lucinda\Framework\SingletonRepository;

/**
 * Binds STDOUT MVC API with Web Security API + OAuth2 Client API for authentication and authorization
 */
class SecurityListener extends \Lucinda\STDOUT\EventListeners\Request
{
    /**
     * @var Attributes
     */
    protected $attributes;
    
    /**
     * {@inheritDoc}
     * @see \Lucinda\STDOUT\Runnable::run()
     */
    public function run(): void
    {        
        $securityTagRoot = getParentNode($this->application, "security");
        $requestBinder = new RequestBinder($this->request, $this->attributes->getValidPage(), true);
        if ($this->application->getTag("oauth2")->{ENVIRONMENT}) {
            $oauth2Wrapper = new Lucinda\OAuth2\Wrapper($this->application->getTag("oauth2")->xpath("..")[0], ENVIRONMENT);
            $oauth2Drivers = $oauth2Wrapper->getDriver();
            
            $oauth2Binder = new OAuth2Binder($oauth2Drivers);
            $securityWrapper = new Lucinda\WebSecurity\Wrapper($securityTagRoot, $requestBinder->getResult(), $oauth2Binder->getResults());
            $this->attributes->setUserId($securityWrapper->getUserID());
            $this->attributes->setCsrfToken($securityWrapper->getCsrfToken());
            $this->attributes->setAccessToken($securityWrapper->getAccessToken());
            
            if ($userID = $securityWrapper->getUserID()) {
                SingletonRepository::set("oauth2", new OAuth2DriverDetector($securityTagRoot, $oauth2Drivers, $userID));
            }
        } else {
            $securityWrapper = new Lucinda\WebSecurity\Wrapper($securityTagRoot, $requestBinder->getResult(), []);
            $this->attributes->setUserId($securityWrapper->getUserID());
            $this->attributes->setCsrfToken($securityWrapper->getCsrfToken());
            $this->attributes->setAccessToken($securityWrapper->getAccessToken());
        }
    }
}
