<?php
/**
 * Detects mechanisms for authenticated state persistence set in security.persistence XML tag.
 */
class PersistenceDriversDetector {
    private $persistenceDrivers;
    
    /**
     * @param SimpleXMLElement $xml XML that contains security.persistence tag.
     */
    public function __construct(SimpleXMLElement $xml) {
        $this->setPersistenceDrivers($xml);
    }
    
    /**
     * Detects persistence drivers based on XML
     * 
     * @param SimpleXMLElement $xml XML that contains security.persistence tag.
     */
    private function setPersistenceDrivers(SimpleXMLElement $xml) {
        $xml = $xml->security->persistence;
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
    
    /**
     * Gets detected drivers for authenticated state persistence.
     * 
     * @return PersistenceDriver[]
     */
    public function getPersistenceDrivers() {
        return $this->persistenceDrivers;
    }
}