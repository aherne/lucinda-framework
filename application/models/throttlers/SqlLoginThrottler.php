<?php
require_once("BasicLoginThrottler.php");

/**
 * Extension of BasicLoginThrottler that uses an SQL database as storage medium 
 */
class SqlLoginThrottler extends BasicLoginThrottler
{
    private $found;
    
    /**
     * {@inheritDoc}
     * @see BasicLoginThrottler::setCurrentStatus()
     */
    protected function setCurrentStatus($ipAddress, $userName)
    {
        $row = SQL("SELECT attempts, penalty_expiration FROM user_logins WHERE ip=:ip AND username=:username", array(
            ":ip"=>$ipAddress,
            ":username"=>$userName
        ))->toRow();
        if (!empty($row)) {
            $this->attempts = $row["attempts"];
            $this->penaltyExpiration = $row["penalty_expiration"];
            $this->found = true;
        }
    }
    
    /**
     * {@inheritDoc}
     * @see BasicLoginThrottler::persist()
     */
    protected function persist()
    {
        if (!$this->found) {
            SQL("INSERT INTO user_logins (ip, username, attempts, penalty_expiration) VALUES (:ip, :username, :attempts, :penalty_expiration)", array(
                ":ip"=>$this->ipAddress,
                ":username"=>$this->userName,
                ":attempts"=>$this->attempts,
                ":penalty_expiration"=>$this->penaltyExpiration
            ));
        } else {
            SQL("UPDATE user_logins SET attempts=:attempts, penalty_expiration=:penalty_expiration WHERE ip=:ip AND username=:username", array(
                ":ip"=>$this->ipAddress,
                ":username"=>$this->userName,
                ":attempts"=>$this->attempts,
                ":penalty_expiration"=>$this->penaltyExpiration
            ));
        }
    }
}

