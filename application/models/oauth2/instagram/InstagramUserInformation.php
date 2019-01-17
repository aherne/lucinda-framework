<?php
require_once("../AbstractUserInformation.php");

/**
 * Collects information about logged in Instagram user
 */
class InstagramUserInformation extends AbstractUserInformation {
    /**
     * Saves logged in user details received from Instagram.
     *
     * @param string[string] $info
     */
	public function __construct($info) {
		$this->id = $info["data"]["id"];
		$this->name = $info["data"]["full_name"];
		$this->email = null; // instagram doesn't send email
	} 
}