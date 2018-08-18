<?php
class Users  implements UserAuthenticationDAO, UserAuthorizationDAO {
    private $id;
    
    public function isAllowed(PageAuthorizationDAO $page, $httpRequestMethod)
    {
        $preparedStatement = SQLConnectionSingleton::getInstance()->createPreparedStatement();
        $preparedStatement->prepare("SELECT id FROM users_resources WHERE resource_id=:resource AND user_id=:user");
        return $preparedStatement->execute(array(":user"=>$this->id, ":resource"=>$page->getID()))->toValue();
    }

    public function setID($userID)
    {
        $this->id = $userID;
    }

    public function getID()
    {
        return $this->id;
    }

    public function login($username, $password, $rememberMe = null)
    {
        $preparedStatement = SQLConnectionSingleton::getInstance()->createPreparedStatement();
        $preparedStatement->prepare("SELECT id FROM users WHERE name=:name AND password=:password");
        return $preparedStatement->execute(array(":name"=>$username, ":password"=>md5($password)))->toValue();
    }
    
    public function logout($userID)
    {}

    
}