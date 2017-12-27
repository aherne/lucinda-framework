<?php
require_once("AbstractUserInformation.php");

class GoogleUserInformation extends AbstractUserInformation {
	public function __construct($info) {
		$this->id = $info["id"];
		$this->name = $info["displayName"];
		$this->email = $info["emails"][0]["value"];
		// gender
	} 
}