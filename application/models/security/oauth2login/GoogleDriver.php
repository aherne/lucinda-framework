<?php
require_once("GoogleResponseWrapper.php");

class GoogleDriver extends OAuth2\Driver {
	const AUTHORIZATION_ENDPOINT_URL = "https://accounts.google.com/o/oauth2/auth";
	const TOKEN_ENDPOINT_URL = "https://accounts.google.com/o/oauth2/token";
	
	protected function getServerInformation() {
		return new OAuth2\ServerInformation(self::AUTHORIZATION_ENDPOINT_URL, self::TOKEN_ENDPOINT_URL);
	}
	
	protected function getResponseWrapper() {
		return new GoogleResponseWrapper();
	}
}