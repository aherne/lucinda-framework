<?php
require_once("PersistenceDriverWrapper.php");

/**
 * Binds SessionPersistenceDriver @ SECURITY API with settings from configuration.xml @ SERVLETS-API and sets up an object on which one can
 * forward session persistence operations.
 */
class SessionPersistenceDriverWrapper extends PersistenceDriverWrapper {
	const DEFAULT_PARAMETER_NAME = "uid";

	/**
	 * {@inheritDoc}
	 * @see PersistenceDriverWrapper::setDriver()
	 */
	protected function setDriver(SimpleXMLElement $xml) {
		$parameterName = (string) $xml["parameter_name"];
		if(!$parameterName) $parameterName = self::DEFAULT_PARAMETER_NAME;

		$expirationTime = (integer) $xml["expiration"];
		$isHttpOnly = (integer) $xml["is_http_only"];
		$isHttpsOnly = (integer) $xml["is_https_only"];

		$this->driver = new SessionPersistenceDriver($parameterName,$expirationTime,$isHttpOnly,$isHttpsOnly);
	}
}