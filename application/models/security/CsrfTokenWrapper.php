<?php
/**
 * Binds SynchronizerToken @ SECURITY-API to settings from configuration.xml @ SERVLETS-API then sets up an object based on which
 * one can perform CSRF checks later on in application's lifecycle.
 */
class CsrfTokenWrapper {
	const DEFAULT_EXPIRATION = 10*60;
	
	private $secret;
	private $expiration;
	
	private $token;
	
	/**
	 * Creates an object
	 * 
	 * @param SimpleXMLElement $xml Contents of security.csrf XML tag.
	 * @throws ApplicationException If 'secret' key is not defined
	 */
	public function __construct(SimpleXMLElement $xml) {
		// sets secret
		$secret = (string) $xml["secret"];
		if(!$secret) throw new ApplicationException("'secret' attribute not set in security.csrf tag");
		
		// sets token
		$this->token = new SynchronizerToken($_SERVER["REMOTE_ADDR"], $secret);
		
		// sets expiration
		$expiration = (string) $xml["expiration"];
		if(!$expiration) $expiration = self::DEFAULT_EXPIRATION;
		$this->expiration = $expiration;		
	}
	
	/**
	 * Encodes a token based on unique user identifier
	 * @param mixed $userID Unique user identifier (usually an integer)
	 * @return string Value of synchronizer token.
	 */
	public function generate($userID) {
		return $this->token->encode($userID, $this->expiration);
	}
	
	/**
	 * Checks if a token is valid for specific uuid.
	 * 
	 * @param string $token Value of synchronizer token
	 * @param mixed $userID Unique user identifier (usually an integer)
	 * @return boolean
     * @throws TokenException If token fails validations.
     * @throws TokenRegenerationException If token needs to be refreshed
	 */
	public function isValid($token, $userID) {
		try {
			$tokenUserID = $this->token->decode($token);
			if($tokenUserID == $userID) {
				return true;
			} else {
				return false;
			}
		} catch(Exception $e) {
			return false;
		}
	}
}