<?php
require_once("../AbstractUserInformation.php");

/**
 * Collects information about logged in Google user
 */
class GoogleUserInformation extends AbstractUserInformation {
    /**
     * Saves logged in user details received from Google.
     *
     * @param string[string] $info
     */
	public function __construct($info) {
		$this->id = $info["id"];
		$this->name = $info["displayName"];
		$this->email = $info["emails"][0]["value"];
	} 
}