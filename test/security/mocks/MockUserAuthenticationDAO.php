<?php
class MockUserAuthenticationDAO implements UserAuthenticationDAO {
	private $users = array(
			1=>array("username"=>"asd","password"=>"fgh")
	);
	
	public function login($username, $password, $rememberMe=null) {
		foreach($this->users as $id=>$info) {
			if($info["username"]==$username && $info["password"]==$password) {
				return $id;
			}
		}
		return null;
	}
	
	public function logout($userID) {
		
	}
}