<?php
require_once("AbstractSecurityDriver.php");
require_once("YandexUserInformation.php");

/**
 * Binds OAuth2\Driver @ OAuth2Client API with OAuth2Driver @ Security API for Yandex
 */
class YandexSecurityDriver extends AbstractSecurityDriver implements OAuth2Driver {
	// login-related constants
	const SCOPES = array();
	const RESOURCE_URL = "https://login.yandex.ru/info";
	
	/**
	 * {@inheritDoc}
	 * @see OAuth2Driver::getUserInformation()
	 */
	public function getUserInformation($accessToken) {
		return new YandexUserInformation($this->driver->getResource($accessToken, self::RESOURCE_URL));
	}
	
	/**
	 * {@inheritDoc}
	 * @see OAuth2Driver::getDefaultScopes()
	 */
	public function getDefaultScopes() {
		return self::SCOPES;
	}
}