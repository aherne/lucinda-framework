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
        $this->name = $info["displayName"];
        $this->email = (!empty($info["emails"][0]["value"])?$info["emails"][0]["value"]:"");
    }
}
