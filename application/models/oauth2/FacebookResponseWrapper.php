<?php
class FacebookResponseWrapper extends OAuth2\ResponseWrapper {
	public function wrap($response) {
		$result = json_decode($response,true);
		if(json_last_error()!=JSON_ERROR_NONE) {
			throw new OAuth2\ServerException("Response is not JSON: ".$response);
		}
		if(!empty($result["error"])) {
			throw new OAuth2\ServerException($result["error"]["message"]);
		}
		$this->response = $result;
	}
}