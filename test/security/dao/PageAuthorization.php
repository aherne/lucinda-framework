<?php
class PageAuthorization implements PageAuthorizationDAO {
    const PAGE_IDS = array(
        array("id"=>1, "path"=>"index", "public"=>0),
        array("id"=>2, "path"=>"login", "public"=>1),
        array("id"=>3, "path"=>"logout", "public"=>1),
        array("id"=>4, "path"=>"private", "public"=>0)
    );
    private $id;
    
    public function isPublic()
    {
        foreach(self::PAGE_IDS as $info) {
            if($info["id"]==$this->id) {
                return $info["public"];
            }
        }
    }
    
    public function setID($path)
    {
        foreach(self::PAGE_IDS as $info) {
            if($info["path"]==$path) {
                $this->id = $info["id"];
            }
        }
    }
    
    public function getID()
    {
        return $this->id;
    }    

    
}