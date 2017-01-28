<?php
class Users implements User  {
	private $userID = 0;
	
	public function __construct($userID = 0) {
		$this->userID = $userID;
	}
	
	public function isAllowed(Page $page) {
		
	}
	public function login(LoginCredentials $credentials) {
		
	}
	
	public function logout() {
		
	}
	
	public function getId() {
		
	}
}