<?php
use OAuth2\AccessTokenResponse;

class MockDriver extends OAuth2\Driver implements OAuth2Login {		
	public function getAccessToken($authorizationCode) {
		if($authorizationCode=="qwertyz") {
			throw new OAuth2\ServerException("double spend!");
		} else if($authorizationCode=="qwertyt") {
			return new OAuth2\AccessTokenResponse(array("access_token"=>"xyzb","scope"=>"hjk","token_type"=>"bearer"));
		} else {
			return new OAuth2\AccessTokenResponse(array("access_token"=>"xyz","scope"=>"hjk","token_type"=>"bearer"));
		}		
	}
	
	protected function getServerInformation() {
		return new OAuth2\ServerInformation("https://www.test.com", "https://www.test.com/token");
	}

	protected function getResponseWrapper() {
		return null;
	}
	
	public function login($accessToken) {
		if($accessToken=="xyzb") throw new OAuth2\ServerException("cannot login!");
		return new OAuth2UserInformation(1, "test", "a@a.com");
	}
	
	public function getDefaultScopes() {
		return array("x","y");
	}
}