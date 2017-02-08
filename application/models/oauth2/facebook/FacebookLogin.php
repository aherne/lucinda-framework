<?php
require_once("FacebookDriver.php");
require_once("FacebookUserInformation.php");
require_once("../OAuth2Login.php");

class FacebookLogin implements OAuth2Login {
	const SCOPES = array("public_profile","email");
	const RESOURCE_URL = "https://graph.facebook.com/v2.8/me";
	const RESOURCE_FIELDS = array("id","name","email");
	private $driver;
	private $accessToken;
	
	public function __construct(OAuth2\ClientInformation $clientInformation) {
		$this->driver = new FacebookDriver($clientInformation);
	}
	
	public function getAuthorizationCode() {
		$this->driver->getAuthorizationCode(self::SCOPES);
	}
	
	public function login($authorizationCode) {
		$response = $this->driver->getAccessToken($authorizationCode);
		$this->accessToken = $response->getAccessToken();
		$info = $this->driver->getResource($this->accessToken, self::RESOURCE_URL, self::RESOURCE_FIELDS);
		return new FacebookUserInformation($info["id"], $info["name"], $info["email"]);
	}
	
	public function getAccessToken() {
		return $this->accessToken;
	}
}