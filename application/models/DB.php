<?php
class DB {
    public static function execute($strQuery, $boundParameters=array()) {
        $preparedStatement = DatabaseConnectionSingleton::getInstance()->createPreparedStatement();
        $preparedStatement->prepare($strQuery);
        foreach($boundParameters as $strParameter=>$mixValue) {
            $preparedStatement->bind($strParameter, $mixValue);
        }
        return $preparedStatement->execute();
    }
}