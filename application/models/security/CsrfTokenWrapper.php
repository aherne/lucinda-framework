<?php

class CsrfTokenWrapper {
	const DEFAULT_EXPIRATION = 10*60;
	
	private $secret;
	private $expiration;
	
	private $token;
	
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
	
	public function generate($userID) {
		return $this->token->encode($userID, $this->expiration);
	}
	
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