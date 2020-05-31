<?php
use Lucinda\WebSecurity\Request;

/**
 * Extension of BasicLoginThrottler that uses a NoSQL database as storage medium
 */
class NoSqlLoginThrottler extends \Lucinda\Framework\AbstractLoginThrottler
{
    const EXPIRATION = 3600;
    private $key;
   
    /**
     * Registers variables, calculates key to search for, and checks current throttling status for request.
     *
     * @param \Lucinda\STDOUT\Request $request
     * @param string $ipAddress IP address detected from client.
     * @param string $userName Username client tries to login with.
     */
    public function __construct(Request $request, string $userName)
    {
        $this->key = "logins__".sha1(json_encode(array("ip"=>$request->getIpAddress(), "username"=>$userName)));
        parent::__construct($request, $userName);
    }
    
    /**
     * {@inheritDoc}
     * @see \Lucinda\WebSecurity\Authentication\Form\LoginThrottler::setCurrentStatus()
     */
    protected function setCurrentStatus(): void
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
     * @see \Lucinda\Framework\AbstractLoginThrottler::persist()
     */
    protected function persist(): void
    {
        $connection = \Lucinda\NoSQL\ConnectionSingleton::getInstance();
        $connection->set($this->key, json_encode(array(
            "attempts"=>$this->attempts,
            "penalty_expiration"=>$this->penaltyExpiration
        ), self::EXPIRATION));
    }
}
