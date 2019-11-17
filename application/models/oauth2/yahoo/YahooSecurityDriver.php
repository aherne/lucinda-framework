<?php
require("YahooUserInformation.php");

/**
 * Binds OAuth2\Driver @ OAuth2Client API with OAuth2Driver @ Security API for Yahoo
 */
class YahooSecurityDriver extends \Lucinda\Framework\AbstractSecurityDriver implements \Lucinda\WebSecurity\OAuth2Driver
{
    // login-related constants
    const SCOPES = array("sdpp-w");
    const RESOURCE_URL = "https://social.yahooapis.com/v1/user/me/profile";
    
    /**
     * {@inheritDoc}
     * @see \Lucinda\WebSecurity\OAuth2Driver::getUserInformation()
     */
    public function getUserInformation($accessToken)
    {
        return new YahooUserInformation($this->driver->getResource($accessToken, self::RESOURCE_URL));
    }
    
    /**
     * {@inheritDoc}
     * @see \Lucinda\WebSecurity\OAuth2Driver::getDefaultScopes()
     */
    public function getDefaultScopes()
    {
        return self::SCOPES;
    }
}
