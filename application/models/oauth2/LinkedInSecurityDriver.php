<?php
require_once("AbstractSecurityDriver.php");
require_once("LinkedInUserInformation.php");

/**
 * Binds OAuth2\Driver @ OAuth2Client API with OAuth2Driver @ Security API for Linkedin
 */
class LinkedInSecurityDriver extends AbstractSecurityDriver implements Lucinda\WebSecurity\OAuth2Driver {
	// login-related constants
	const SCOPES = array("r_basicprofile","r_emailaddress");
	const RESOURCE_URL = "https://api.linkedin.com/v1/people/~";
	const RESOURCE_URL_EMAIL = "https://api.linkedin.com/v1/people/~/email-address";

	/**
	 * {@inheritDoc}
	 * @see Lucinda\WebSecurity\OAuth2Driver::getUserInformation()
	 */
	public function getUserInformation($accessToken) {
		$info = $this->driver->getResource($accessToken, self::RESOURCE_URL);
		$info["email"] = $this->driver->getResource($accessToken, self::RESOURCE_URL_EMAIL);
		return new LinkedinUserInformation($info);
	}
	
	/**
	 * {@inheritDoc}
	 * @see Lucinda\WebSecurity\OAuth2Driver::getDefaultScopes()
	 */
	public function getDefaultScopes() {
		return self::SCOPES;
	}
}