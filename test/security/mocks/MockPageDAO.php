<?php
class MockPageDAO implements PageAuthorizationDAO {
	private $id;
	private $pages = array(
		"asd"=>array("is_public"=>1,"id"=>1),
		"fgh"=>array("is_public"=>0,"id"=>2),
		"jkl"=>array("is_public"=>0,"id"=>3)
	);
	
    public function isPublic() {
    	foreach($this->pages as $page) {
    		if($page["id"]==$this->id) {
    			return $page["is_public"];
    		}
    	}
    }
    
    public function setID($path) {
    	if(isset($this->pages[$path])) {
    		$this->id = $this->pages[$path]["id"];
    	}
    }
    
    public function getID() {
    	return $this->id;
    }
}