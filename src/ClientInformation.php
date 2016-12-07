<?php
namespace OAuth2;

/**
 * Encapsulates information about OAuth2 client.
 */
class ClientInformation {
	private $appID;
	private $appSecret;
	private $siteURL;
	
	public function __construct($clientID, $clientSecret, $siteURL) {
		$this->appID = $clientID;
		$this->appSecret = $clientSecret;
		$this->siteURL = $siteURL;
	}
	
	/**
	 * Gets unique client/application ID.
	 * 
	 * @return string
	 */
	public function getApplicationID() {
		return $this->appID;
	}

	/**
	 * Gets private client secret.
	 *
	 * @return string
	 */
	public function getApplicationSecret() {
		return $this->appSecret;
	}
	
	/**
	 * Gets client default callback URL.
	 *
	 * @return string
	 */
	public function getSiteURL() {
		return $this->siteURL;
	}
}