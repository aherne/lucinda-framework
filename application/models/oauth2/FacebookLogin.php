<?php
require_once("FacebookDriver.php");
require_once("UserInformation.php");

class FacebookLogin {
	const SCOPES = array("public_profile","email");
	const RESOURCE_URL = "https://graph.facebook.com/v2.8/me";
	const RESOURCE_FIELDS = array("id","name","email");
	private $driver;
	
	public function __construct(OAuth2\ClientInformation $clientInformation) {
		$this->driver = new FacebookDriver($clientInformation);
	}
	
	public function getAuthorizationCode() {
		$this->driver->getAuthorizationCode(self::SCOPES);
	}
	
	public function login($authorizationCode) {
		$response = $this->driver->getAccessToken($authorizationCode);
		$info = $this->driver->getResource($response->getAccessToken(), self::RESOURCE_URL, self::RESOURCE_FIELDS);
		return new UserInformation($info["id"], $info["name"], $info["email"]);
	}
}