<?php
require_once("FacebookResponseWrapper.php");

/**
 * Implements Facebook OAuth2 driver.
 */
class FacebookDriver extends OAuth2\Driver {
	const AUTHORIZATION_ENDPOINT_URL = "https://www.facebook.com/v2.8/dialog/oauth";
	const TOKEN_ENDPOINT_URL = "https://graph.facebook.com/v2.8/oauth/access_token";
	
	/**
	 * {@inheritDoc}
	 * @see OAuth2Login::getAuthorizationEndpoint()
	 */
	protected function getServerInformation() {
		return new OAuth2\ServerInformation(self::AUTHORIZATION_ENDPOINT_URL, self::TOKEN_ENDPOINT_URL);
	}

	/**
	 * {@inheritDoc}
	 * @see OAuth2Login::getAuthorizationEndpoint()
	 */
	protected function getResponseWrapper() {
		return new FacebookResponseWrapper();
	}
}