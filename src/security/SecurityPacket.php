<?php
/**
 * Holds information about authentication/authorization outcomes to be latter rendered (generally as a redirection).
 */
class SecurityPacket extends Exception {
	private $callback;
	private $status;
	private $accessToken;
	
	/**
	 * Sets path to redirect to.
	 * 
	 * @param string $callback
	 */
	public function setCallback($callback) {
		$this->callback = $callback;
	}
	
	/**
	 * Gets path to redirect to.
	 * 
	 * @return string
	 */
	public function getCallback() {
		return $this->callback;
	}
	
	/**
	 * Sets redirection reason.
	 * 
	 * @param integer $status
	 */
	public function setStatus($status) {
		$result = "";
		switch($status) {
			case AuthenticationResultStatus::LOGIN_OK:
				$result= "login_ok";
				break;
			case AuthenticationResultStatus::LOGOUT_OK:
				$result= "logout_ok";
				break;
			case AuthenticationResultStatus::DEFERRED:
				$result= "redirect";
				break;
			case AuthenticationResultStatus::LOGIN_FAILED:
				$result= "login_failed";
				break;
			case AuthenticationResultStatus::LOGOUT_FAILED:
				$result= "logout_failed";
				break;
			case AuthorizationResultStatus::UNAUTHORIZED:
				$result= "unauthorized";
				break;
			case AuthorizationResultStatus::FORBIDDEN:
				$result= "forbidden";
				break;
			case AuthorizationResultStatus::NOT_FOUND:
				$result= "not_found";
				break;
			default:
				break;
		}
		$this->status = $result;
	}
	
	/**
	 * Gets redirection reason.
	 * 
	 * @return string
	 */
	public function getStatus() {
		return $this->status;
	}
	
	/**
	 * Sets access token (useful for stateless applications).
	 * 
	 * @param mixed $userID Authenticated user id.
	 * @param PersistenceDriver[] $persistenceDrivers List of persistence drivers registered.
	 */
	public function setAccessToken($userID, $persistenceDrivers) {
		$token = "";
		if($userID) {
			foreach($persistenceDrivers as $persistenceDriver) {
				if($persistenceDriver instanceof TokenPersistenceDriver) {
					$token = $persistenceDriver->getAccessToken();
				}
			}
		}
		$this->accessToken = $token;
	}
	
	/**
	 * Gets access token. In order to stay authenticated, each request will have to include this as a header.
	 * 
	 * @return string
	 */
	public function getAccessToken() {
		return $this->accessToken;
	}
}
