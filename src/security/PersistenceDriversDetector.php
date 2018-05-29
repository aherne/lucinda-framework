<?php
class PersistenceDriversDetector {
    private $persistenceDrivers;
    
    public function __construct(Application $application) {
        $this->setPersistenceDrivers($application);
    }
    
    
    private function setPersistenceDrivers(Application $application) {
        $xml = $application->getXML()->security->persistence;
        if(empty($xml)) return; // it is allowed for elements to not persist
        
        if($xml->session) {
            require_once("persistence_drivers/SessionPersistenceDriverWrapper.php");
            $wrapper = new SessionPersistenceDriverWrapper($xml->session);
            $this->persistenceDrivers[] = $wrapper->getDriver();
        }
        
        if($xml->remember_me) {
            require_once("persistence_drivers/RememberMePersistenceDriverWrapper.php");
            $wrapper = new RememberMePersistenceDriverWrapper($xml->remember_me);
            $this->persistenceDrivers[] = $wrapper->getDriver();
        }
        
        if($xml->synchronizer_token) {
            require_once("persistence_drivers/SynchronizerTokenPersistenceDriverWrapper.php");
            $wrapper = new SynchronizerTokenPersistenceDriverWrapper($xml->synchronizer_token);
            $this->persistenceDrivers[] = $wrapper->getDriver();
        }
        
        if($xml->json_web_token) {
            require_once("persistence_drivers/JsonWebTokenPersistenceDriverWrapper.php");
            $wrapper = new JsonWebTokenPersistenceDriverWrapper($xml->json_web_token);
            $this->persistenceDrivers[] = $wrapper->getDriver();
        }
    }
    
    public function getPersistenceDrivers() {
        return $this->persistenceDrivers;
    }
}