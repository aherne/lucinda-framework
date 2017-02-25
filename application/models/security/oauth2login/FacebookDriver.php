<?php
require_once("FacebookResponseWrapper.php");

/**
 * Implements Facebook OAuth2 driver.
 */
class FacebookDriver extends OAuth2\Driver implements OAuth2Login {
	const AUTHORIZATION_ENDPOINT_URL = "https://www.facebook.com/v2.8/dialog/oauth";
	const TOKEN_ENDPOINT_URL = "https://graph.facebook.com/v2.8/oauth/access_token";

	// login-related constants
	const SCOPES = array("public_profile","email");
	const RESOURCE_URL = "https://graph.facebook.com/v2.8/me";
	const RESOURCE_FIELDS = array("id","name","email");
	
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
		return new FacebookResponseWrapper();
	}
	
	/**
	 * {@inheritDoc}
	 * @see OAuth2Login::login()
	 */
	public function login($accessToken) {
		$info = $this->getResource($accessToken, self::RESOURCE_URL, self::RESOURCE_FIELDS);
		return new OAuth2UserInformation($info["id"], $info["name"], $info["email"]);
	}
	
	/**
	 * {@inheritDoc}
	 * @see OAuth2Login::getDefaultScopes()
	 */
	public function getDefaultScopes() {
		return self::SCOPES;
	}
}