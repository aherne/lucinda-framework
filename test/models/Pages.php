<?php
class Pages implements PageAuthorizationDAO {
    private $id;
    
    public function isPublic()
    {
        $preparedStatement = SQLConnectionSingleton::getInstance()->createPreparedStatement();
        $preparedStatement->prepare("SELECT is_public FROM resources WHERE id=:id");
        return $preparedStatement->execute(array(":id"=>$this->id))->toValue();
    }

    public function setID($path)
    {
        $preparedStatement = SQLConnectionSingleton::getInstance()->createPreparedStatement();
        $preparedStatement->prepare("SELECT id FROM resources WHERE url=:url");
        $this->id = $preparedStatement->execute(array(":url"=>$path))->toValue();
    }

    public function getID()
    {
        return $this->id;
    }

    
}