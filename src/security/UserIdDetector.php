<?php
class UserIdDetector
{
    private $userID;
    
    public function __construct($persistenceDrivers) {
        $this->setUserID($persistenceDrivers);  
    }
    
    private function setUserID($persistenceDrivers) {
        foreach($persistenceDrivers as $persistenceDriver) {
            $this->userID = $persistenceDriver->load();
            if($this->userID) {
                break;
            }
        }
    }
    
    public function getUserID() {
        return $this->userID;
    }
}

