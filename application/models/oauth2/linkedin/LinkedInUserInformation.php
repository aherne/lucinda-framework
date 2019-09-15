<?php
/**
 * Collects information about logged in LinkedIn user
 */
class LinkedInUserInformation extends \Lucinda\Framework\AbstractUserInformation
{
    /**
     * Saves logged in user details received from LinkedIn.
     *
     * @param string[string] $info
     */
    public function __construct($info)
    {
        $this->id = $info["id"];
        $this->name = $info["firstName"]." ".$info["lastName"];
        $this->email = (!empty($info["email"])?$info["email"]:"");
    }
}
