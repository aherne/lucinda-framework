<?php
require_once("application/models/Json.php");

/**
 * Implements parsing of GOOGLE OAUTH2 API response
 */
class FacebookResponseWrapper extends OAuth2\ResponseWrapper {
	public function wrap($response) {
		$json = new Json();
		$result = $json->decode($response,true);
		if(!empty($result["error"])) {
			throw new OAuth2\ServerException($result["error"]["message"]);
		}
		$this->response = $result;
	}
}