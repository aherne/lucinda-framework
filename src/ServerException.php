<?php
namespace OAuth2;

/**
 * Encapsulates error response received from OAuth2 server according to RFC6749
 */
class ServerException extends \Exception {
	private $errorCode;
	private $errorDescription;
	private $errorURL;
	private $state;
	
	/**
	 * Sets error code received from server.
	 * 
	 * @param string $errorCode
	 */
	public function setErrorCode($errorCode) {
		$this->errorCode = $errorCode;
	}
	
	/**
	 * Gets error code received from server.
	 * 
	 * @return string
	 */
	public function getErrorCode() {
		return $this->errorCode;
	}

	/**
	 * Sets error description received from server.
	 *
	 * @param string $errorDescription
	 */
	public function setErrorDescription($errorDescription) {
		$this->errorDescription = $errorDescription;
	}

	/**
	 * Gets error description received from server.
	 *
	 * @return string
	 */
	public function getErrorDescription() {
		return $this->errorDescription;
	}
	
	/**
	 * Sets URI of web page with information about the error received from server
	 * 
	 * @param string $errorURL
	 */
	public function setErrorURL($errorURL) {
		$this->errorURL = $errorURL;
	}

	/**
	 * Gets URI of web page with information about the error received from server
	 *
	 * @return string
	 */
	public function getErrorURL() {
		return $this->errorURL;
	}
	
	/**
	 * Sets opaque value used by the client to maintain state between the request and callback received from server
	 * 
	 * @param string $scope
	 */
	public function setState($state) {
		$this->state = $state;
	}
	
	/**
	 * Gets opaque value used by the client to maintain state between the request and callback received from server
	 * 
	 * @return string
	 */
	public function getState() {
		return $this->state;
	}
}