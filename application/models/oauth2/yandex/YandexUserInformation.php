<?php
require_once("../AbstractUserInformation.php");

/**
 * Collects information about logged in Yandex user
 */
class YandexUserInformation extends AbstractUserInformation {
    /**
     * Saves logged in user details received from Yandex.
     *
     * @param string[string] $info
     */
	public function __construct($info) {
		$this->id = $info["id"];
		$this->name = $info["first_name"]." ".$info["last_name"];
		$this->email = $info["default_email"];
	} 
}