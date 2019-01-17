<?php
/**
 * Implements parsing of LinkedIn OAUTH2 API response
 */
class LinkedInResponseWrapper extends \OAuth2\ResponseWrapper {
	/**
	 * {@inheritDoc}
	 * @see \OAuth2\ResponseWrapper::wrap()
	 */
	public function wrap($response) {
		$result = json_decode($response,true);
		if(json_last_error() != JSON_ERROR_NONE) {
			throw new OAuth2\ServerException(json_last_error_msg());
		}
		if(!empty($result["error"])) {
			// error when authorization code is invalid
			$exception = new OAuth2\ServerException($result["error_description"]);
			$exception->setErrorCode($result["error"]);
			$exception->setErrorDescription($result["error_description"]);
			throw $exception;
		} else if(!empty($result["message"])) {
			// error when access token is invalid or in retrieving resource
			throw new OAuth2\ServerException($result["message"]);
		}
		$this->response = $result;
	}
}