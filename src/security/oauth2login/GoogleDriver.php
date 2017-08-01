<?php
require_once("GoogleResponseWrapper.php");

/**
 * Implements Google OAuth2 driver.
 */
class GoogleDriver extends OAuth2\Driver implements OAuth2Login {
	const AUTHORIZATION_ENDPOINT_URL = "https://accounts.google.com/o/oauth2/auth";
	const TOKEN_ENDPOINT_URL = "https://accounts.google.com/o/oauth2/token";
	
	// login-related constants
	const SCOPES = array("https://www.googleapis.com/auth/plus.login","https://www.googleapis.com/auth/plus.profile.emails.read");
	const RESOURCE_URL = "https://www.googleapis.com/plus/v1/people/me";
	const RESOURCE_FIELDS = array("id","name","emails");

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
	
	/**
	 * {@inheritDoc}
	 * @see OAuth2Login::login()
	 */
	public function login($accessToken) {
		$info = $this->getResource($accessToken, self::RESOURCE_URL, self::RESOURCE_FIELDS);
		return new OAuth2UserInformation($info["id"], $info["name"]["givenName"]." ".$info["name"]["familyName"], $info["emails"][0]["value"]);
	}
	
	/**
	 * {@inheritDoc}
	 * @see OAuth2Login::getDefaultScopes()
	 */
	public function getDefaultScopes() {
		return self::SCOPES;
	}
}