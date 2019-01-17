<?php
require_once("GitHubResponseWrapper.php");

/**
 * Implements GitHub OAuth2 driver on top of \OAuth2\Driver architecture
 */
class GitHubDriver extends \OAuth2\Driver {
	const AUTHORIZATION_ENDPOINT_URL = "https://github.com/login/oauth/authorize";
	const TOKEN_ENDPOINT_URL = "https://github.com/login/oauth/access_token";
	
	private $appName;
	
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
		return new GithubResponseWrapper();
	}
	
	/**
	 * Sets name of remote GitHub application. MANDATORY for resources retrieval!
	 * 
	 * @param string $applicationName
	 */
	public function setApplicationName($applicationName) {
		$this->appName = $applicationName;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \OAuth2\Driver::getResource()
	 */
	public function getResource($accessToken, $resourceURL, $fields=array()) {
		if(!$this->appName) throw new OAuth2\ClientException("Setting application name is mandatory to retrieve GitHub resources!");
		$responseWrapper = $this->getResponseWrapper();
		$we = new OAuth2\WrappedExecutor($responseWrapper);
		$we->setHttpMethod(OAuth2\HttpMethod::GET);
		$we->addHeader("Authorization", "token ".$accessToken);
		$we->setUserAgent($this->appName);
		$parameters = (!empty($fields)?array("fields"=>implode(",",$fields)):array());
		$we->execute($resourceURL, $parameters);
		return $responseWrapper->getResponse();
	}
}