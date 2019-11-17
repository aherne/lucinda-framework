<?php
require("YandexUserInformation.php");

/**
 * Binds OAuth2\Driver @ OAuth2Client API with OAuth2Driver @ Security API for Yandex
 */
class YandexSecurityDriver extends \Lucinda\Framework\AbstractSecurityDriver implements \Lucinda\WebSecurity\OAuth2Driver
{
    // login-related constants
    const SCOPES = array();
    const RESOURCE_URL = "https://login.yandex.ru/info";
    
    /**
     * {@inheritDoc}
     * @see \Lucinda\WebSecurity\OAuth2Driver::getUserInformation()
     */
    public function getUserInformation($accessToken)
    {
        return new YandexUserInformation($this->driver->getResource($accessToken, self::RESOURCE_URL));
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
