<?php
/**
 * Defines an abstract authentication mechanism that works with AuthenticationResult
 */
abstract class AuthenticationWrapper {
	protected $result;

	/**
	 * Sets authentication result.
	 *
	 * @param AuthenticationResult $result Holds a reference to an object that encapsulates authentication result.
	 * @param string $sourcePage Callback path to redirect to on failure.
	 * @param string $targetPage Callback path to redirect to on success.
	 */
	protected function setResult(AuthenticationResult $result, $sourcePage, $targetPage) {
		if($result->getStatus()==AuthenticationResultStatus::LOGIN_OK || $result->getStatus()==AuthenticationResultStatus::LOGOUT_OK) {
			$result->setCallbackURI($targetPage);
		} else {
			$result->setCallbackURI($sourcePage);
		}
		$this->result = $result;
	}
	
	/**
	 * Gets authentication result.
	 *
	 * @return AuthenticationResult
	 */
	public function getResult() {
		return $this->result;
	}
}