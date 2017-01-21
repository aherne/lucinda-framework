<?php
require_once("application/models/dao/Users.php");
require_once("application/models/dao/Resources.php");
require_once("application/models/dao/ConfirmedChanges.php");

class AuthorizeException extends Exception {}

/**
 * email, password, id, resources
 */
class AuthorizeController extends Controller {
    public function run() {
        // get user id
        $users = new Users();
        $userInfo = $users->login($_POST["email"], $_POST["password"]);
        if($userInfo==null) throw new AuthorizeException("Invalid credentials!");
        $userID = $userInfo->id;
        
        // get resource id
        $resources = new Resources();
        $resourceID = $resources->getId($_POST["resource"]);
        
        // check if user is allowed to resource
        if(!$users->isAllowed($userID, $resourceID)) throw new AuthorizeException("Insufficient rights!");
        
        // get user's rights
        $cc = new ConfirmedChanges();
        $cc->save($userID, $_SESSION["user_id"], $resourceID, $_POST["id"], session_id(), $_POST["value"]);
    }
}