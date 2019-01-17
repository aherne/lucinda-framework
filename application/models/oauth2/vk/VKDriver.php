<?php
require_once("VKResponseWrapper.php");

/**
 * Implements VK OAuth2 driver on top of \OAuth2\Driver architecture
 */
class VKDriver extends \OAuth2\Driver {
	const AUTHORIZATION_ENDPOINT_URL = "https://oauth.vk.com/authorize";
	const TOKEN_ENDPOINT_URL = "https://oauth.vk.com/access_token";
		
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
		return new VKResponseWrapper();
	}
		
	/**
	 * {@inheritDoc}
	 * @see \OAuth2\Driver::getResource()
	 */
	public function getResource($accessToken, $resourceURL, $fields=array()) {
		$responseWrapper = $this->getResponseWrapper();
		$we = new OAuth2\WrappedExecutor($responseWrapper);
		$we->setHttpMethod(OAuth2\HttpMethod::GET);
		$fields["access_token"] = $accessToken;
		$we->execute($resourceURL, $fields);
		return $responseWrapper->getResponse();
	}
}