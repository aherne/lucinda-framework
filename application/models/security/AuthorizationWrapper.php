<?php
/**
 * Defines an abstract authorization mechanism that works with AuthenticationResult
 */
abstract class AuthorizationWrapper {
	private $result;
	
	/**
	 * Sets result of authorization attempt.
	 * 
	 * @param AuthorizationResult $result
	 */
	protected function setResult(AuthorizationResult $result) {
		$this->result = $result;
	}
	
	/**
	 * Gets result of authorization attempt
	 * 
	 * @return AuthorizationResult
	 */
	public function getResult() {
		return $this->result;
	}
}