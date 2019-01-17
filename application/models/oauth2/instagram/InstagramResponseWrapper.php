<?php
/**
 * Implements parsing of Instagram OAUTH2 API response
 */
class InstagramResponseWrapper extends \OAuth2\ResponseWrapper {
	/**
	 * {@inheritDoc}
	 * @see \OAuth2\ResponseWrapper::wrap()
	 */
	public function wrap($response) {
		$result = json_decode($response,true);
		if(json_last_error() != JSON_ERROR_NONE) {
			throw new OAuth2\ServerException(json_last_error_msg());
		}
		if(!empty($result["meta"]["error_message"])) {
			throw new OAuth2\ServerException($result["meta"]["error_message"]);
		}
		$this->response = $result;
	}
}