<?php
require_once("OAuth2Login.php");
require_once("UserOauth2AuthenticationDAO.php");
require_once("UserInformation.php");

class Oauth2Authentication {
	private $dao;
	private $persistenceDrivers;
	
	public function __construct(UserOauth2AuthenticationDAO $dao, $persistenceDrivers) {
		// check argument that it's instance of PersistenceDriver
		foreach($persistenceDrivers as $persistentDriver) {
			if(!($persistentDriver instanceof PersistenceDriver)) throw new AuthenticationException("Items must be instanceof PersistenceDriver");
		}
		
		$this->dao = $dao;
		$this->persistenceDrivers = $persistenceDrivers;
	}
	
	public function login(OAuth2Login $driver, $authorizationCode="", $createUserIfNotExists=false) {
		if(!$authorizationCode) {
			$this->driver->getAuthorizationCode();
			return;
		}
		// query dao for a user id and an authorization code >> redirect to temporary page
		$userInformation = $driver->login($authorizationCode);
		if($userInformation==null) return null;
		$userID = $this->dao->login($userInformation, $this->driver->getAccessToken(), $createUserIfNotExists);
		// save in persistence drivers
		if(!empty($userID)) {
			foreach($this->persistenceDrivers as $persistentDriver) {
				$persistentDriver->save($userID);
			}
		}
		return $userID;
	}
	
	public function logout() {
		// detect user_id from persistence drivers
		$userID = null;
		foreach($this->persistenceDrivers as $persistentDriver) {
			$userID = $persistentDriver->load();
			if($userID) break;
		}
		if(!$userID) throw new AuthenticationException("No logged in state was detected!");
		
		// perform operations
		$this->dao->logout($userID);
		$this->persistenceDriver->clear($userID);
	}
}