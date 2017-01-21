<?php
require_once("application/models/DB.php");
require_once("application/models/dao/Panels.php");
require_once("application/models/dao/Users.php");

/**
 * Encapsulates basic authorization for CMS.
 */
class BasicAuthorization {
    const STATUS_OK = "OK";
    
    
    private $page;
    private $status;
    
    /**
     * Performs authorization logic.
     * 
     * @param integer $user_id Id of user that must be autorized
     * @param string $page_url Url of page (must be found @ DB in panels.url)
     */
    public function __construct($user_id, $page_url) {
        $object = new Panels();
        $panel = $object->getInfoByURL($page_url);
        if($panel) {
            if(!$panel->isPublic) {
                if($user_id) {
                    $object = new Users();
                    if(!$object->isAllowed($user_id, $panel->id)) {
                        $this->page = "index";
                        $this->status = "NOT_ALLOWED";
                    } else {
                        // ok: do nothing
                        $this->status = BasicAuthorization::STATUS_OK;
                    }
                } else {
                    $this->page = "login";
                    $this->status = "NOT_ALLOWED";
                }
            } else {
                // do nothing: it is allowed by default to display public panels
                $this->status = BasicAuthorization::STATUS_OK;
            }
        } else {
            $page = "";
            if($user_id) {
                $page = "index";
            } else {
                $page = "login";
            }
            $this->page = $page;
            $this->status = "NOT_FOUND";
        }
    }
    
    /**
     * Gets page to redirect to when status is different from "OK"
     * 
     * @return string
     */
    public function getPage() {
        return $this->page;
    }
    
    /**
     * Gets status of failed authorization.
     * 
     * @return string
     */
    public function getStatus() {
        return $this->status;
    }
}