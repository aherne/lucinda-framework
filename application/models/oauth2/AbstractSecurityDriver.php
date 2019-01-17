<?php
/**
 * Binds OAuth2\Driver @ OAuth2Client API with OAuth2Driver @ Security API in order to collect remote user information.
 */
abstract class AbstractSecurityDriver {
	protected $driver;

    /**
     * Saves OAuth2 driver supplied, to perform operations on it later on.
     *
     * @param \OAuth2\Driver $driver
     */
	public function __construct(OAuth2\Driver $driver) {
		$this->driver = $driver;
	}
}