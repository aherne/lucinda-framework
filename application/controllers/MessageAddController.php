<?php
require_once("application/models/dao/Messages.php");

class MessageAddController extends Controller {
	public function run() {
		$entry = new MessageEntry();
		$entry->code = $_POST["code"];
		$entry->message = $_POST["body"];
		$entry->isError = $_POST["isError"];
		

		$messages = new Messages();
		$messages->add($entry);
	}
}