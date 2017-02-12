<?php
require_once("libraries/php-security-api/src/authentication/SessionPersistenceDriver.php");
require_once("PersistenceDriverWrapper.php");

class SessionPersistenceDriverWrapper extends PersistenceDriverWrapper {
	const DEFAULT_PARAMETER_NAME = "uid";
	
	protected function setDriver(SimpleXMLElement $xml) {
		$parameterName = (string) $xml->parameter;
		if(!$parameterName) $parameterName = self::DEFAULT_PARAMETER_NAME;
		
		$expirationTime = (integer) $xml->expiration;
		$isHttpOnly = (integer) $xml->is_http_only;
		$isHttpsOnly = (integer) $xml->is_https_only;
		
		$this->driver = new SessionPersistenceDriver($parameterName,$expirationTime,$isHttpOnly,$isHttpsOnly);
	}
}