<?php
/**
 * Collects information about logged in VKontakte user
 */
class VKUserInformation extends \Lucinda\Framework\AbstractUserInformation
{
    /**
     * Saves logged in user details received from VKontakte.
     *
     * @param string[string] $info
     */
    public function __construct($info)
    {
        $this->id = $info["response"][0]["uid"];
        $this->name = $info["response"][0]["first_name"]." ".$info["response"][0]["last_name"];
        $this->email = null; // driver doesn't send email
    }
}
