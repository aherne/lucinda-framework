<?php
class UserAuthorization implements UserAuthorizationDAO {
    private $userID;
    
    public function isAllowed(PageAuthorizationDAO $page, $httpRequestMethod)
    {
        return $page->isPublic() || ($this->userID && $page->getID()==1);
    }

    public function setID($userID)
    {
        $this->userID = $userID;
    }

    public function getID()
    {
        return $this->userID;
    }    
}