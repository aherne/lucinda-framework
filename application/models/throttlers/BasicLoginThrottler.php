<?php
/**
 * Basic time based throttler against serial failed login attempts:
 * - unit of verification is ip address and username
 * - each failed login is penalized for two power attempts number seconds penalty
 * - if another login comes in penalty period, user automatically gets LOGIN_FAILED status
 */
abstract class BasicLoginThrottler extends \Lucinda\Framework\LoginThrottler
{
    const PENALTY_QUOTIENT = 2;
    protected $ipAddress;
    protected $userName;
    protected $attempts = 0;
    protected $penaltyExpiration;
    
    /**
     * Registers variables and checks current throttling status for request.
     * 
     * @param \Lucinda\MVC\STDOUT\Request $request Encapsulated request made by client.
     * @param string $ipAddress IP address detected from client.
     * @param string $userName Username client tries to login with.
     */
    public function __construct(\Lucinda\MVC\STDOUT\Request $request, $ipAddress, $userName)
    {
        $this->ipAddress = $ipAddress;
        $this->userName = $userName;
        $this->setCurrentStatus($ipAddress, $userName);
    }
    
    /**
     * {@inheritDoc}
     * @see \Lucinda\Framework\LoginThrottler::getTimePenalty()
     */
    public function getTimePenalty()
    {
        return ($this->penaltyExpiration && strtotime($this->penaltyExpiration) > time()?strtotime($this->penaltyExpiration)-time():0);
    }
    
    /**
     * {@inheritDoc}
     * @see \Lucinda\Framework\LoginThrottler::setFailure()
     */
    public function setFailure()
    {
        $this->attempts++;
        $this->penaltyExpiration = ($this->attempts>1?date("Y-m-d H:i:s", time() + pow(self::PENALTY_QUOTIENT, $this->attempts-1)):null);
        $this->persist();
    }
    
    /**
     * {@inheritDoc}
     * @see \Lucinda\Framework\LoginThrottler::setSuccess()
     */
    public function setSuccess()
    {
        $this->attempts = 0;
        $this->penaltyExpiration = null;
        $this->persist();
    }
    
    /**
     * {@inheritDoc}
     * @see \Lucinda\Framework\LoginThrottler::setCurrentStatus()
     */
    abstract protected function setCurrentStatus($ipAddress, $userName);
    
    /**
     * Persists login attempts in a storage medium (SQL or NoSQL database)
     */
    abstract protected function persist();
}

