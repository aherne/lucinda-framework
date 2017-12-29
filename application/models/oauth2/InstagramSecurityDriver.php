<?php
require_once("AbstractSecurityDriver.php");
require_once("InstagramUserInformation.php");

/**
 * Binds OAuth2\Driver @ OAuth2Client API with OAuth2Driver @ Security API for Instagram
 */
class InstagramSecurityDriver extends AbstractSecurityDriver implements OAuth2Driver {
	// login-related constants
	const SCOPES = array();
	const RESOURCE_URL = "https://api.instagram.com/v1/users/self/";

	/**
	 * {@inheritDoc}
	 * @see OAuth2Driver::getUserInformation()
	 */
	public function getUserInformation($accessToken) {
		return new InstagramUserInformation($this->driver->getResource($accessToken, self::RESOURCE_URL));
	}
	
	/**
	 * {@inheritDoc}
	 * @see OAuth2Driver::getDefaultScopes()
	 */
	public function getDefaultScopes() {
		return self::SCOPES;
	}
}