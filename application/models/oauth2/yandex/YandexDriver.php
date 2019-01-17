<?php
require_once("YandexResponseWrapper.php");

/**
 * Implements Yandex OAuth2 driver on top of \OAuth2\Driver architecture.
 */
class YandexDriver extends \OAuth2\Driver {
	const AUTHORIZATION_ENDPOINT_URL = "https://oauth.yandex.com/authorize";
	const TOKEN_ENDPOINT_URL = "https://oauth.yandex.com/token";
	
	/**
	 * {@inheritDoc}
	 * @see \OAuth2\Driver::getServerInformation()
	 */
	protected function getServerInformation() {
		return new OAuth2\ServerInformation(self::AUTHORIZATION_ENDPOINT_URL, self::TOKEN_ENDPOINT_URL);
	}

	/**
	 * {@inheritDoc}
	 * @see \OAuth2\Driver::getResponseWrapper()
	 */
	protected function getResponseWrapper() {
		return new YandexResponseWrapper();
	}
}