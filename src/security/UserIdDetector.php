<?php
/**
 * Detects logged in unique user identifier from persistence drivers.
 */
class UserIdDetector
{
    private $userID;
    
    /**
     * @param PersistenceDriver[] $persistenceDrivers List of persistence drivers to detect from.
     */
    public function __construct($persistenceDrivers) {
        $this->setUserID($persistenceDrivers);  
    }
    
    /**
     * Saves detected unique user identifier from persistence drivers.
     * 
     * @param PersistenceDriver[] $persistenceDrivers List of persistence drivers to detect from.
     */
    private function setUserID($persistenceDrivers) {
        foreach($persistenceDrivers as $persistenceDriver) {
            $this->userID = $persistenceDriver->load();
            if($this->userID) {
                break;
            }
        }
    }
    
    /**
     * Gets detected unique user identifier
     * 
     * @return integer|string
     */
    public function getUserID() {
        return $this->userID;
    }
}

