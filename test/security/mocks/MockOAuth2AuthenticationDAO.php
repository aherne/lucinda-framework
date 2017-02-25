<?php
class MockOAuth2AuthenticationDAO implements OAuth2AuthenticationDAO {
	private $accessToken;
	private $users;
	
    public function login(OAuth2UserInformation $userInformation, $accessToken, $createIfNotExists=true)  {
    	return 11;
    }
    
    public function logout($userID) {
    	
    }
    
    public function getAccessToken($userID) {
    	
    }
	
}