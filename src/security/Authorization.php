<?php
require_once("DAOLocator.php");
require_once("SecurityPacket.php");

class Authorization {
    public function __construct(Application $application, Request $request) {
        $wrapper = $this->getWrapper($application, $request);
        $this->authorize($wrapper, $request);
    }
    
    private function getWrapper(Application $application, Request $request) {
        $xml = $application->getXML()->security->authorization;
        if(empty($xml)) {
            throw new ApplicationException("Entry missing in configuration.xml: security.authentication");
        }
        
        $wrapper = null;
        if($xml->by_route) {
            require_once("authorization/XMLAuthorizationWrapper.php");
            $wrapper = new XMLAuthorizationWrapper(
                $application->getXML(),
                $request->getValidator()->getPage(),
                $request->getAttribute("user_id"));
        }
        if($xml->by_dao) {
            require_once("authorization/DAOAuthorizationWrapper.php");
            $wrapper = new DAOAuthorizationWrapper(
                $application->getXML(),
                $request->getValidator()->getPage(),
                $request->getAttribute("user_id"));
        }
        return $wrapper;
    }
    
    private function authorize(AuthorizationWrapper $wrapper, Request $request) {
        if($wrapper) {
            if($wrapper->getResult()->getStatus() == AuthorizationResultStatus::OK) {
                // authorization was successful
                return;
            } else {
                // authorization failed
                $transport = new SecurityPacket();
                $transport->setCallback($request->getURI()->getContextPath()."/".$wrapper->getResult()->getCallbackURI());
                $transport->setStatus($wrapper->getResult()->getStatus());
                throw $transport;
            }
        } else {
            throw new ApplicationException("No authorization driver found in configuration.xml: security.authentication");
        }
    }
}