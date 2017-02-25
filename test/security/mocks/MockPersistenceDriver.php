<?php
class MockPersistenceDriver implements PersistenceDriver {
	private $userID;
	
	public function load(){
		return $this->userID;
	}
	
	public function save($userID){
		$this->userID = $userID;
	}
	
	public function clear($userID){
		$this->userID = null;
	}
}