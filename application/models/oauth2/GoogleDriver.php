<?php
require_once("GoogleResponseWrapper.php");

class GoogleDriver {
	const AUTHORIZATION_ENDPOINT_URL = "https://accounts.google.com/o/oauth2/auth";
	const TOKEN_ENDPOINT_URL = "https://accounts.google.com/o/oauth2/token";
	private $driver;
	
	public function __construct(OAuth2\ClientInformation $clientInformation) {
		// create server information object
		$serverInformation = new OAuth2\ServerInformation(self::AUTHORIZATION_ENDPOINT_URL, self::TOKEN_ENDPOINT_URL);
		$this->driver = new Oauth2\Driver($clientInformation, $serverInformation);
	}
	
	public function getAuthorizationCode($scopes) {
		$this->driver->getAuthorizationCode($scopes);
	}
	
	public function getAccessToken($authorizationCode) {
		return $this->driver->getAccessToken($authorizationCode, new GoogleResponseWrapper());
	}
	
	public function getResource($accessToken, $resourceURL, $fields=array()) {
		return $this->driver->getResource($accessToken, $resourceURL, $fields, new GoogleResponseWrapper());
	}
}