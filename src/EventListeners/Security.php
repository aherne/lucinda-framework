<?php
namespace Lucinda\Project\EventListeners;

use Lucinda\WebSecurity\Wrapper as SecurityWrapper;
use Lucinda\OAuth2\Wrapper as OAuth2Wrapper;
use Lucinda\Framework\RequestBinder;
use Lucinda\Framework\OAuth2\Binder as OAuth2Binder;
use Lucinda\Framework\OAuth2\DriverDetector as OAuth2DriverDetector;
use Lucinda\Framework\SingletonRepository;
use Lucinda\STDOUT\EventListeners\Request;
use Lucinda\Project\Attributes;

require_once(dirname(__DIR__, 2)."/helpers/getRemoteResource.php");
require_once(dirname(__DIR__, 2)."/helpers/getParentNode.php");


/**
 * Binds STDOUT MVC API with Web Security API + OAuth2 Client API for authentication and authorization
 */
class Security extends Request
{
    /**
     * @var Attributes
     */
    protected $attributes;

    /**
     * {@inheritDoc}
     * @see \Lucinda\MVC\Runnable::run()
     */
    public function run(): void
    {
        $securityTagRoot = \getParentNode($this->application, "security");
        $requestBinder = new RequestBinder($this->request, $this->attributes->getValidPage(), true);
        if ($this->application->getTag("oauth2")->{ENVIRONMENT}) {
            $oauth2Wrapper = new OAuth2Wrapper($this->application->getTag("oauth2")->xpath("..")[0], ENVIRONMENT);
            $oauth2Drivers = $oauth2Wrapper->getDriver();

            $oauth2Binder = new OAuth2Binder($oauth2Drivers);
            $securityWrapper = new SecurityWrapper($securityTagRoot, $requestBinder->getResult(), $oauth2Binder->getResults());
            $this->attributes->setUserId($securityWrapper->getUserID());
            $this->attributes->setCsrfToken($securityWrapper->getCsrfToken());
            $this->attributes->setAccessToken($securityWrapper->getAccessToken());

            if ($userID = $securityWrapper->getUserID()) {
                SingletonRepository::set("oauth2", new OAuth2DriverDetector($securityTagRoot, $oauth2Drivers, $userID));
            }
        } else {
            $securityWrapper = new SecurityWrapper($securityTagRoot, $requestBinder->getResult(), []);
            $this->attributes->setUserId($securityWrapper->getUserID());
            $this->attributes->setCsrfToken($securityWrapper->getCsrfToken());
            $this->attributes->setAccessToken($securityWrapper->getAccessToken());
        }
    }
}
