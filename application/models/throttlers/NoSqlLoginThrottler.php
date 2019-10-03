<?php
require_once("BasicLoginThrottler.php");

/**
 * Extension of BasicLoginThrottler that uses a NoSQL database as storage medium
 */
class NoSqlLoginThrottler extends BasicLoginThrottler
{
    const EXPIRATION = 3600;
    private $key;
   
    /**
     * Registers variables, calculates key to search for, and checks current throttling status for request.
     * 
     * @param \Lucinda\MVC\STDOUT\Request $request
     * @param string $ipAddress IP address detected from client.
     * @param string $userName Username client tries to login with.
     */
    public function __construct(\Lucinda\MVC\STDOUT\Request $request, $ipAddress, $userName)
    {
        $this->ipAddress = $ipAddress;
        $this->userName = $userName;
        $this->key = "logins__".sha1(json_encode(array("ip"=>$ipAddress, "username"=>$userName)));
        $this->setCurrentStatus($ipAddress, $userName);
    }
    
    /**
     * {@inheritDoc}
     * @see BasicLoginThrottler::setCurrentStatus()
     */
    protected function setCurrentStatus($ipAddress, $userName)
    {
        $connection = \Lucinda\NoSQL\ConnectionSingleton::getInstance();
        if ($connection->contains($this->key)) {
            $value = $connection->get($this->key);
            if (!$value) {
                $connection->delete($this->key);
                return;
            }
            
            $row = json_decode($value, true);
            $this->attempts = $row["attempts"];
            $this->penaltyExpiration = $row["penalty_expiration"];
        }
    }
    
    /**
     * {@inheritDoc}
     * @see BasicLoginThrottler::persist()
     */
    protected function persist()
    {
        $connection = \Lucinda\NoSQL\ConnectionSingleton::getInstance();
        $connection->set($this->key, json_encode(array(
            "attempts"=>$this->attempts,
            "penalty_expiration"=>$this->penaltyExpiration
        ), self::EXPIRATION));
    }
}