<?php
require_once("GoogleDriver.php");
require_once("UserInformation.php");

class GoogleLogin {
	const SCOPES = array("https://www.googleapis.com/auth/plus.login","https://www.googleapis.com/auth/plus.profile.emails.read");
	const RESOURCE_URL = "https://www.googleapis.com/plus/v1/people/me";
	const RESOURCE_FIELDS = array("id","name","emails");
	private $driver;
	
	public function __construct(OAuth2\ClientInformation $clientInformation) {
		$this->driver = new GoogleDriver($clientInformation);
	}
	
	public function getAuthorizationCode() {
		$this->driver->getAuthorizationCode(self::SCOPES);
	}
	
	public function login($authorizationCode) {
		$response = $this->driver->getAccessToken($authorizationCode);
		$info = $this->driver->getResource($response->getAccessToken(), self::RESOURCE_URL, self::RESOURCE_FIELDS);
		return new UserInformation($info["id"], $info["name"]["givenName"]." ".$info["name"]["familyName"], $info["emails"][0]["value"]);
	}
}