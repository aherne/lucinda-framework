<?php
require("GoogleUserInformation.php");

/**
 * Binds OAuth2\Driver @ OAuth2Client API with OAuth2Driver @ Security API for Google
 */
class GoogleSecurityDriver extends \Lucinda\Framework\AbstractSecurityDriver implements \Lucinda\WebSecurity\OAuth2Driver
{
    // login-related constants
    const SCOPES = array("https://www.googleapis.com/auth/plus.login","https://www.googleapis.com/auth/plus.profile.emails.read");
    const RESOURCE_URL = "https://www.googleapis.com/plus/v1/people/me";

    /**
     * {@inheritDoc}
     * @see \Lucinda\WebSecurity\OAuth2Driver::getUserInformation()
     */
    public function getUserInformation($accessToken)
    {
        return new GoogleUserInformation($this->driver->getResource($accessToken, self::RESOURCE_URL));
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
