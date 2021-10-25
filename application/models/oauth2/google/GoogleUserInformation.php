<?php
/**
 * Collects information about logged in Google user
 */
class GoogleUserInformation extends \Lucinda\Framework\AbstractUserInformation
{
    /**
     * Saves logged in user details received from Google.
     *
     * @param string[string] $info
     */
    public function __construct($info)
    {
        $this->id = $info["id"];
        $this->name = $info["name"];
        $this->email = $info["email"];
    }
}
