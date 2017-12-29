<?php
require_once("AbstractSecurityDriver.php");
require_once("GitHubUserInformation.php");

/**
 * Binds OAuth2\Driver @ OAuth2Client API with OAuth2Driver @ Security API for Github
 */
class GitHubSecurityDriver extends AbstractSecurityDriver implements OAuth2Driver {
	// login-related constants
	const SCOPES = array("read:user","user:email");
	const RESOURCE_URL = "https://api.github.com/user";
	const RESOURCE_URL_EMAIL = "https://api.github.com/user/emails";

	/**
	 * {@inheritDoc}
	 * @see OAuth2Driver::getUserInformation()
	 */
	public function getUserInformation($accessToken) {
		$info = $this->driver->getResource($accessToken, self::RESOURCE_URL);
		$tmp = $this->driver->getResource($accessToken, self::RESOURCE_URL_EMAIL);
		$info["email"] = $tmp[0]["email"];
		return new GitHubUserInformation($info);
	}
	
	/**
	 * {@inheritDoc}
	 * @see OAuth2Driver::getDefaultScopes()
	 */
	public function getDefaultScopes() {
		return self::SCOPES;
	}
}