<?php
/**
 * Collects information about logged in Yahoo user
 */
class YahooUserInformation extends \Lucinda\Framework\AbstractUserInformation
{
    /**
     * Saves logged in user details received from Yahoo.
     *
     * @param array $info
     */
    public function __construct($info)
    {
        $this->id = $info["profile"]["guid"];
        $this->name = $info["profile"]["familyName"]." ".$info["profile"]["givenName"];
        $this->email = $info["profile"]["emails"]["handle"];
    }
}
