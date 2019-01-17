<?php
require_once("../AbstractUserInformation.php");

/**
 * Collects information about logged in GitHub user
 */
class GitHubUserInformation extends AbstractUserInformation {
    /**
     * Saves logged in user details received from GitHub.
     *
     * @param string[string] $info
     */
	public function __construct($info) {
		$this->id = $info["id"];
		$this->name = $info["name"];
		$this->email = $info["email"];
	} 
}