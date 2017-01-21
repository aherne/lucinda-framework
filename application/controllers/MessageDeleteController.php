<?php
require_once("application/models/dao/Messages.php");

class MessageDeleteController extends Controller {
	public function run() {
		$messages = new Messages();
		$messages->delete($_POST["id"]);
	}
}