<?php
class MockUserAuthorizationDAO implements UserAuthorizationDAO {
	private $id;

	private $allowed_pages = array(
			1=>array(2),
			2=>array(3)
	);
	
    public function isAllowed(PageAuthorizationDAO $page) {
    	$pageID = $page->getID();
    	foreach($this->allowed_pages[$this->id] as $tempPageID) {
    		if($pageID == $tempPageID) return true;
    	}
    	return false;
    }
    
    public function setID($userID) {
    	$this->id = $userID;
    }
    
    public function getID() {
    	return $this->id;
    }
}