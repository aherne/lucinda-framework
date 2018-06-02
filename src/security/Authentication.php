<?php
require_once("DAOLocator.php");
require_once("SecurityPacket.php");

class Authentication {
    public function __construct(Application $application, Request $request, $persistenceDrivers) {
        $wrapper = $this->getWrapper($application, $request, $persistenceDrivers);
        $this->authenticate($wrapper, $request, $persistenceDrivers);
    }
    
    private function getWrapper(Application $application, Request $request, $persistenceDrivers) {
        $xml = $application->getXML()->security->authentication;
        if(empty($xml)) {
            throw new ApplicationException("Entry missing in configuration.xml: security.authentication");
        }
        
        $wrapper = null;
        if($xml->form) {
            if((string) $xml->form["dao"]) {
                require_once("authentication/DAOAuthenticationWrapper.php");
                $wrapper = new DAOAuthenticationWrapper(
                    $application->getXML(),
                    $request->getValidator()->getPage(),
                    $persistenceDrivers,
                    $request->getAttribute("csrf"));
                
            } else {
                require_once("authentication/XMLAuthenticationWrapper.php");
                $wrapper = new XMLAuthenticationWrapper(
                    $application->getXML(),
                    $request->getValidator()->getPage(),
                    $persistenceDrivers,
                    $request->getAttribute("csrf"));
            }
        }
        if($xml->oauth2) {
            require_once("authentication/Oauth2AuthenticationWrapper.php");
            $wrapper = new Oauth2AuthenticationWrapper(
                $application->getXML(),
                $request->getValidator()->getPage(),
                $persistenceDrivers,
                $request->getAttribute("csrf"));
            // saves oauth2 drivers to be used later on
            $request->setAttribute("oauth2",$wrapper->getDrivers());
        }
        return $wrapper;
    }
    
    private function authenticate(AuthenticationWrapper $wrapper, Request $request, $persistenceDrivers) {
        if($wrapper) {
            if(!$wrapper->getResult()) {
                // no authentication was requested
                return;
            } else {
                // authentication was requested
                $transport = new SecurityPacket();
                $transport->setCallback($wrapper->getResult()->getStatus()==AuthenticationResultStatus::DEFERRED?$wrapper->getResult()->getCallbackURI():$request->getURI()->getContextPath()."/".$wrapper->getResult()->getCallbackURI());
                $transport->setStatus($wrapper->getResult()->getStatus());
                $transport->setAccessToken($wrapper->getResult()->getUserID(), $persistenceDrivers);
                throw $transport;
            }
        } else {
            throw new ApplicationException("No authentication driver found in configuration.xml: security.authentication");
        }
    }
}