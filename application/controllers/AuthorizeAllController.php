<?php
require_once("application/models/dao/Users.php");
require_once("application/models/dao/ConfirmedChanges.php");

class AuthorizeException extends Exception {}

class AuthorizeAllController extends Controller {
	public function run() {
		// get user id
		$users = new Users();
		$userInfo = $users->login($_POST["email"], $_POST["password"]);
		if($userInfo==null) {
			throw new AuthorizeException("Invalid credentials!");
		}
		$userID = $userInfo->id;

		// check if user has rights that match department & level
		if(!$users->belongsTo($userID, $_POST['department'], $_POST['level'])) {
			throw new AuthorizeException("User does not have level ".strtoupper($_POST['level'])."!");
		}

		// get resource id
		$panels = new Panels();
		$lockedResources = $panels->getAllowedLockedResources($_POST["panel"],$userID);
		$cc = new ConfirmedChanges();
		foreach($lockedResources as $id=>$name) {
			$cc->save($userID, $_SESSION["user_id"], $id, $_POST["id"], session_id(), "");			
		}
		$this->response->setAttribute("resources",$lockedResources);
	}
}