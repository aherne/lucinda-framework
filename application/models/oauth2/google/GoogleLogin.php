<?php
require_once("GoogleDriver.php");
require_once("GoogleUserInformation.php");
require_once("../OAuth2Login.php");

class GoogleLogin implements OAuth2Login {
	const SCOPES = array("https://www.googleapis.com/auth/plus.login","https://www.googleapis.com/auth/plus.profile.emails.read");
	const RESOURCE_URL = "https://www.googleapis.com/plus/v1/people/me";
	const RESOURCE_FIELDS = array("id","name","emails");
	private $driver;
	private $accessToken;
	
	public function __construct(OAuth2\ClientInformation $clientInformation) {
		$this->driver = new GoogleDriver($clientInformation);
	}
	
	public function getAuthorizationCode() {
		$this->driver->getAuthorizationCode(self::SCOPES);
	}
	
	public function login($authorizationCode) {
		$response = $this->driver->getAccessToken($authorizationCode);
		$this->accessToken = $response->getAccessToken();
		$info = $this->driver->getResource($this->accessToken, self::RESOURCE_URL, self::RESOURCE_FIELDS);
		return new GoogleUserInformation($info["id"], $info["name"]["givenName"]." ".$info["name"]["familyName"], $info["emails"][0]["value"]);
	}
	
	public function getAccessToken() {
		return $this->accessToken;
	}
}