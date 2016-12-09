<?php
namespace Google;

use OAuth2\ResponseWrapper as OAuth2_ResponseWrapper;
use OAuth2\ServerException as OAuth2_ServerException;

class ResponseWrapper implements OAuth2_ResponseWrapper {
	protected $response;

	public function wrap($response) {
		$result = json_decode($response,true);
		if(json_last_error()!=JSON_ERROR_NONE) {
			throw new OAuth2_ServerException("Invalid response: ".$response);
		}
		if(!empty($result["error"])) {
			throw new OAuth2_ServerException($result["error"]["message"], $result["error"]["code"]);
		}
		$this->response = $result;
	}

	public function getResponse() {
		return $this->response;
	}
}