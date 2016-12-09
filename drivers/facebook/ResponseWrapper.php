<?php
namespace Facebook;

use OAuth2\ResponseWrapper as OAuth2_ResponseWrapper;

class ResponseWrapper implements OAuth2_ResponseWrapper {
	protected $response;

	public function wrap($response) {
		//string(16) "{"success":true}"
		$result = json_decode($response,true);
		if(json_last_error()!=JSON_ERROR_NONE) {
			throw new OAuth2\ServerException("Invalid response: ".$response);
		}
		if(!empty($result["error"])) {
			throw new OAuth2\ServerException($result["error"]["message"], $result["error"]["code"]);
		}
		$this->response = $result;
	}

	public function getResponse() {
		return $this->response;
	}
}