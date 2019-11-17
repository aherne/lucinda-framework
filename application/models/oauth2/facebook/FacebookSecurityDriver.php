<?php
require("FacebookUserInformation.php");

/**
 * Binds OAuth2\Driver @ OAuth2Client API with OAuth2Driver @ Security API for Facebook
 */
class FacebookSecurityDriver extends \Lucinda\Framework\AbstractSecurityDriver implements \Lucinda\WebSecurity\OAuth2Driver
{
    // login-related constants
    const SCOPES = array("public_profile","email");
    const RESOURCE_URL = "https://graph.facebook.com/v2.8/me";
    const RESOURCE_FIELDS = array("id","name","email");
    
    /**
     * {@inheritDoc}
     * @see \Lucinda\WebSecurity\OAuth2Driver::getUserInformation()
     */
    public function getUserInformation($accessToken)
    {
        return new FacebookUserInformation($this->driver->getResource($accessToken, self::RESOURCE_URL, self::RESOURCE_FIELDS));
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
