<?php
require_once("PersistenceDriverWrapper.php");

/**
 * Binds RememberMePersistenceDriver @ SECURITY API with settings from configuration.xml @ SERVLETS-API and sets up an object on which one can
 * forward remember-me cookie operations.
 */
class RememberMePersistenceDriverWrapper extends PersistenceDriverWrapper {
	const DEFAULT_PARAMETER_NAME = "uid";
	const DEFAULT_EXPIRATION_TIME = 24*3600;

	/**
	 * {@inheritDoc}
	 * @see PersistenceDriverWrapper::setDriver()
	 */
	protected function setDriver(SimpleXMLElement $xml) {
		$secret = (string) $xml["secret"];
		if(!$secret) throw new ApplicationException("'secret' key of security.persistence.remember_me tag is mandatory!");

		$parameterName = (string) $xml["parameter_name"];
		if(!$parameterName) $parameterName = self::DEFAULT_PARAMETER_NAME;

		$expirationTime = (integer) $xml["expiration"];
		if(!$expirationTime) $expirationTime = self::DEFAULT_EXPIRATION_TIME;

		$isHttpOnly = (integer) $xml["is_http_only"];
		$isHttpsOnly = (integer) $xml["is_https_only"];

		$this->driver = new RememberMePersistenceDriver($secret, $parameterName,$expirationTime,$isHttpOnly,$isHttpsOnly);
	}
}