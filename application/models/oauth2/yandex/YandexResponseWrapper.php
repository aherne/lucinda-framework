<?php
/**
 * Implements parsing of Yandex OAUTH2 API response
 */
class YandexResponseWrapper extends \OAuth2\ResponseWrapper {
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
			$exception = new OAuth2\ServerException($result["error_description"]);
			$exception->setErrorCode($result["error"]);
			$exception->setErrorDescription($result["error_description"]);
			throw $exception;
		} else if(!$response) {
			throw new OAuth2\ServerException("OAuth2 token is invalid!");
		}
		$this->response = $result;
	}
}