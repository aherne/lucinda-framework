<?php
/**
 * Collects information about logged in Facebook user
 */
class FacebookUserInformation extends \Lucinda\Framework\AbstractUserInformation
{
    /**
     * Saves logged in user details received from Facebook.
     *
     * @param string[string] $info
     */
    public function __construct($info)
    {
        $this->id = $info["id"];
        $this->name = $info["name"];
        $this->email = (!empty($info["email"])?$info["email"]:"");
    }
}
