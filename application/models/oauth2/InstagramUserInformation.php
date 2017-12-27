<?php
require_once("AbstractUserInformation.php");

class InstagramUserInformation extends AbstractUserInformation {
	public function __construct($info) {
		$this->id = $info["data"]["id"];
		$this->name = $info["data"]["full_name"];
		$this->email = null; // instagram doesn't send email
	} 
}