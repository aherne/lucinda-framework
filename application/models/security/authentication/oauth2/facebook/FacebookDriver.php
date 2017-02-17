<?php
require_once("FacebookResponseWrapper.php");

class FacebookDriver {
	const AUTHORIZATION_ENDPOINT_URL = "https://www.facebook.com/v2.8/dialog/oauth";
	const TOKEN_ENDPOINT_URL = "https://graph.facebook.com/v2.8/oauth/access_token";
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
		return $this->driver->getAccessToken($authorizationCode, new FacebookResponseWrapper());
	}
	
	public function getResource($accessToken, $resourceURL, $fields=array()) {
		return $this->driver->getResource($accessToken, $resourceURL, $fields, new FacebookResponseWrapper());
	}
}