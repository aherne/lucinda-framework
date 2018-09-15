<?php
require_once("AbstractSecurityDriver.php");
require_once("VKUserInformation.php");

/**
 * Binds OAuth2\Driver @ OAuth2Client API with OAuth2Driver @ Security API for VK
 */
class VKSecurityDriver extends AbstractSecurityDriver implements Lucinda\WebSecurity\OAuth2Driver {
	// login-related constants
	const SCOPES = array();
	const RESOURCE_URL = "https://api.vk.com/method/users.get";
	
	/**
	 * {@inheritDoc}
	 * @see Lucinda\WebSecurity\OAuth2Driver::getUserInformation()
	 */
	public function getUserInformation($accessToken) {
		return new VKUserInformation($this->driver->getResource($accessToken, self::RESOURCE_URL));
	}
	
	/**
	 * {@inheritDoc}
	 * @see Lucinda\WebSecurity\OAuth2Driver::getDefaultScopes()
	 */
	public function getDefaultScopes() {
		return self::SCOPES;
	}
}