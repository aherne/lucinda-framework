<?php
require_once("OAuth2Login.php");
require_once("UserOauth2AuthenticationDAO.php");

class Oauth2Authentication {
	private $dao;
	private $persistenceDriver;
	private $driver;
	
	public function __construct(OAuth2Login $driver, UserOauth2AuthenticationDAO $dao, PersistenceDriver $persistenceDriver) {
		$this->dao = $dao;
		$this->driver = $driver;
		$this->persistenceDriver = $persistenceDriver;
	}
	
	public function login($authorizationCode="", $createUserIfNotExists=false) {
		if(!$authorizationCode) {
			$this->driver->getAuthorizationCode();
			return;
		}
		// query dao for a user id and an authorization code >> redirect to temporary page
		$userInformation = $this->driver->login($authorizationCode);
		if($userInformation==null) return null;
		$userID = $this->dao->login($userInformation, $this->driver->getAccessToken(), $createUserIfNotExists);
		$this->persistenceDriver->save($userID);
	}
	
	public function logout(PersistenceDriver $pd) {
		$this->dao->logout($userID);
		$this->persistenceDriver->clear($userID);
	}
}