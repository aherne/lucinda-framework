<?php
/**
 * Implements parsing of VK OAUTH2 API response
 */
class VKResponseWrapper extends \OAuth2\ResponseWrapper {
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
			if(isset($result["error"]["error_code"])) {
				// error when retrieving resource
				$exception = new OAuth2\ServerException($result["error"]["error_msg"]);
				$exception->setErrorCode($result["error"]["error_code"]);
				$exception->setErrorDescription($result["error"]["error_msg"]);
				throw $exception;
			} else {
				// error when retrieving access token / when user denies app access
				$exception = new OAuth2\ServerException($result["error_description"]);
				$exception->setErrorCode($result["error"]);
				$exception->setErrorDescription($result["error_description"]);
				throw $exception;
			}
		}
		$this->response = $result;
	}
}