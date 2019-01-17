<?php
require_once("GoogleResponseWrapper.php");

/**
 * Implements Google OAuth2 driver on top of \OAuth2\Driver architecture
 */
class GoogleDriver extends \OAuth2\Driver {
	const AUTHORIZATION_ENDPOINT_URL = "https://accounts.google.com/o/oauth2/auth";
	const TOKEN_ENDPOINT_URL = "https://accounts.google.com/o/oauth2/token";

	/**
	 * {@inheritDoc}
	 * @see \OAuth2\Driver::getServerInformation()
	 */
	protected function getServerInformation() {
		return new OAuth2\ServerInformation(self::AUTHORIZATION_ENDPOINT_URL, self::TOKEN_ENDPOINT_URL);
	}
	
	/**
	 * {@inheritDoc}
	 * @see \OAuth2\Driver::getResponseWrapper()
	 */
	protected function getResponseWrapper() {
		return new GoogleResponseWrapper();
	}
}