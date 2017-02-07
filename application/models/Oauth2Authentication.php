<?php
/**
 * 1. 
 */
class Oauth2Authentication {
	private $driver;
	
	public function __construct(SimpleXMLElement $xml) {
		// get oauth2 driver @ xml
		// get client information @ xml & construct ClientInformation object
		// get server information based on driver
		// construct a driver object
	}
	
	public function login($authorizationCode, PersistenceDriver $pd) {
		// query dao for a user id and an authorization code >> redirect to temporary page
		
	}
	
	public function logout(PersistenceDriver $pd) {
		
	}
}