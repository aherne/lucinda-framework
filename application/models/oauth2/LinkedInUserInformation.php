<?php
require_once("AbstractUserInformation.php");

class LinkedInUserInformation extends AbstractUserInformation {
	public function __construct($info) {
		$this->id = $info["id"];
		$this->name = $info["firstName"]." ".$info["lastName"];
		$this->email = $info["email"];
	} 
}