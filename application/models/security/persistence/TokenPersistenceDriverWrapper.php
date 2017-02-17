<?php
require_once("PersistenceDriverWrapper.php");

class TokenPersistenceDriverWrapper extends PersistenceDriverWrapper {
	const DEFAULT_EXPIRATION_TIME = 3600;
	const DEFAULT_REGENERATION_TIME = 60;

	protected function setDriver(SimpleXMLElement $xml) {
		$secret = (string) $xml["secret"];
		if(!$secret) throw new ServletApplicationException("'secret' key of security.persistence.token tag is mandatory!");

		$expirationTime = (integer) $xml["expiration"];
		if(!$expirationTime) $expirationTime = self::DEFAULT_EXPIRATION_TIME;

		$regenerationTime = (integer) $xml["regeneration"];
		if(!$regenerationTime) $regenerationTime = self::DEFAULT_REGENERATION_TIME;

		$this->driver = new TokenPersistenceDriver($secret, $expirationTime, $regenerationTime);
	}
}