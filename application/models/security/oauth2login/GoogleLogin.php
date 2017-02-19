<?php
/**
 * Implements login via Facebook.
 */
class GoogleLogin implements OAuth2Login {
	const SCOPES = array("https://www.googleapis.com/auth/plus.login","https://www.googleapis.com/auth/plus.profile.emails.read");
	const RESOURCE_URL = "https://www.googleapis.com/plus/v1/people/me";
	const RESOURCE_FIELDS = array("id","name","emails");
	private $driver;
	private $accessToken;

	/**
	 * Creates an object.
	 *
	 * @param OAuth2\ClientInformation $clientInformation Encapsulates information that proves your application's identity to Google.
	 */
	public function __construct(OAuth2\ClientInformation $clientInformation) {
		$this->driver = new GoogleDriver($clientInformation);
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
		return new OAuth2UserInformation($info["id"], $info["name"]["givenName"]." ".$info["name"]["familyName"], $info["emails"][0]["value"]);
	}
	
	/**
	 * {@inheritDoc}
	 * @see OAuth2Login::getAccessToken()
	 */
	public function getAccessToken() {
		return $this->accessToken;
	}
}