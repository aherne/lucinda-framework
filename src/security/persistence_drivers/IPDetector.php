<?php
/**
 * Detects client IP based on contents of $_SERVER superglobal
 */
class IPDetector {
    private $ip;
    
    /**
     * Kick starts ip detection process
     */
    public function __construct() {
        $this->setIP();
    }
    
    /**
     * Performs IP detection and saves result.
     */
    private function setIP() {
        $ip_keys = array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED');
        foreach ($ip_keys as $key) {
            if (array_key_exists($key, $_SERVER) === true) {
                foreach (explode(',', $_SERVER[$key]) as $ip) {
                    // trim for safety measures
                    $ip = trim($ip);
                    
                    // attempt to validate IP
                    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) {
                        $this->ip = $ip;
                        return;
                    }
                }
            }
        }
        $this->ip = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : false;
    }
    
    /**
     * Gets detected client IP address
     * 
     * @return string
     */
    public function getIP() {
        return $this->ip;
    }
}