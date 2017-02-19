<?php
/**
 * Implements login via Facebook.
 */
class FacebookLogin implements OAuth2Login {
	const SCOPES = array("public_profile","email");
	const RESOURCE_URL = "https://graph.facebook.com/v2.8/me";
	const RESOURCE_FIELDS = array("id","name","email");
	private $driver;
	private $accessToken;
	
	/**
	 * Creates an object.
	 * 
	 * @param OAuth2\ClientInformation $clientInformation Encapsulates information that will be sent to Facebook to authenticat your application.
	 */
	public function __construct(OAuth2\ClientInformation $clientInformation) {
		$this->driver = new FacebookDriver($clientInformation);
	}
	
	/**
	 * {@inheritDoc}
	 * @see OAuth2Login::getAuthorizationEndpoint()
	 */
	public function getAuthorizationEndpoint() {
		return $this->driver->getAuthorizationEndpoint(self::SCOPES);
	}
	
	/**
	 * {@inheritDoc}
	 * @see OAuth2Login::login()
	 */
	public function login($authorizationCode) {
		$response = $this->driver->getAccessToken($authorizationCode);
		$this->accessToken = $response->getAccessToken();
		$info = $this->driver->getResource($this->accessToken, self::RESOURCE_URL, self::RESOURCE_FIELDS);
		return new OAuth2UserInformation($info["id"], $info["name"], $info["email"]);
	}
	
	/**
	 * {@inheritDoc}
	 * @see OAuth2Login::getAccessToken()
	 */
	public function getAccessToken() {
		return $this->accessToken;
	}
}