<?php
require_once("AbstractUserInformation.php");

class VKUserInformation extends AbstractUserInformation {
	public function __construct($info) {
		$this->id = $info["response"][0]["id"];
		$this->name = $info["response"][0]["first_name"]." ".$info["response"][0]["last_name"];
	} 
}