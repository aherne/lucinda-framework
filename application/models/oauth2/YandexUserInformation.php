<?php
require_once("AbstractUserInformation.php");

class YandexUserInformation extends AbstractUserInformation {
	public function __construct($info) {
		$this->id = $info["id"];
		$this->name = $info["first_name"]." ".$info["last_name"];
		$this->email = $info["default_email"];
	} 
}