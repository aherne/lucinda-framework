<?php
require_once("InstagramResponseWrapper.php");

/**
 * Implements Instagram OAuth2 driver on top of \OAuth2\Driver architecture
 */
class InstagramDriver extends \OAuth2\Driver {
	const AUTHORIZATION_ENDPOINT_URL = "https://api.instagram.com/oauth/authorize/";
	const TOKEN_ENDPOINT_URL = "https://api.instagram.com/oauth/access_token";
	
	/**
	 * {@inheritDoc}
	 * @see \OAuth2\Driver::getServerInformation()
	 */
	protected function getServerInformation() {
		return new OAuth2\ServerInformation(self::AUTHORIZATION_ENDPOINT_URL, self::TOKEN_ENDPOINT_URL);
	}

	/**
	 * {@inheritDoc}
	 * @see \OAuth2\Driver::getResponseWrapper()
	 */
	protected function getResponseWrapper() {
		return new InstagramResponseWrapper();
	}
		
	/**
	 * {@inheritDoc}
	 * @see \OAuth2\Driver::getAccessToken()
	 */
	public function getAccessToken($authorizationCode) {
		$responseWrapper = $this->getResponseWrapper();
		$we = new OAuth2\WrappedExecutor($responseWrapper);
		$we->setHttpMethod(OAuth2\HttpMethod::POST);
		$we->addHeader("Content-Type", "application/x-www-form-urlencoded");
		$atr = new OAuth2\AccessTokenRequest($this->serverInformation->getTokenEndpoint());
		$atr->setClientInformation($this->clientInformation);
		$atr->setCode($authorizationCode);
		$atr->setRedirectURL($this->clientInformation->getSiteURL());
		$atr->execute($we);
		$response = $responseWrapper->getResponse();
		if(!empty($response["error_message"])) {
			throw new OAuth2\ServerException($response["error_message"]);
		}
		return new OAuth2\AccessTokenResponse($response);
	}
		
	/**
	 * {@inheritDoc}
	 * @see \OAuth2\Driver::getResource()
	 */
	public function getResource($accessToken, $resourceURL, $fields=array()) {
		$responseWrapper = $this->getResponseWrapper();
		$we = new OAuth2\WrappedExecutor($responseWrapper);
		$we->setHttpMethod(OAuth2\HttpMethod::GET);
		$fields["client_id"] = $this->clientInformation->getApplicationID();
		$fields["access_token"] = $accessToken;
		$we->execute($resourceURL, $fields);
		return $responseWrapper->getResponse();
	}
}