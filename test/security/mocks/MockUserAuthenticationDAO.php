<?php
class MockUserAuthenticationDAO implements UserAuthenticationDAO {
	private $users = array(
			1=>array("username"=>"asd","password"=>"fgh")
	);
	
	public function login(LoginCredentials $credentials) {
		foreach($this->users as $id=>$info) {
			if($info["username"]==$credentials->getUserName() && $info["password"]==$credentials->getPassword()) {
				return $id;
			}
		}
		return null;
	}
	
	public function logout($userID) {
		
	}
}