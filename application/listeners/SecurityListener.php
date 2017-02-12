<?php
require_once("application/models/DAOLocator.php");
/**
 * 

		<persistence>
			<session parameter_name="..." expiration_time="0" http_only="0" https_only="0" handler="{CLASS_PATH}" save_path="{SAVE_PATH}"/>
			<token expirationTime="0" regenerationTime="0"/>
			<remember_me parameter_name="..."  expiration_time="0" http_only="0" https_only="0"/>
		</persistence>
 * @author aherne
 *
 */
class SecurityListener extends RequestListener {
	private $daoLocator;
	private $persistenceDrivers = array();
	
	public function run() {
		$this->setDAOLocator();
		$this->setPersistenceDrivers();
		$this->authenticate();
		$this->authorize();
		// TODO: generate/check csrf key
	}
	
	private function setDAOLocator() {
		$this->daoLocator = new DAOLocator($this->application->getXML());
	}
	
	private function setPersistenceDrivers() {
		$xml = $this->application->getXML()->security->persistence;
		if(empty($xml)) return; // it is allowed for elements to not persist
		
		if($xml->session) {
			
		}
		
		if($xml->remember_me) {
			
		}
		
		// TODO: add later support for tokens (who do not support redirection)
	}
	
	private function authenticate() {
		$xml = $this->application->getXML()->security->authentication;
		if(empty($xml)) throw new ServletApplicationException("Entry missing in configuration.xml: security.authentication");
		
		if($xml->form) {
			require_once("application/models/FormAuthenticationWrapper.php");
			new FormAuthenticationWrapper($xml->form, $this->request->getAttribute("page_url"), $this->persistenceDrivers, $this->daoLocator);
		}
		if($xml->oauth2) {
			require_once("application/models/Oauth2AuthenticationWrapper.php");
			new Oauth2AuthenticationWrapper($xml->oauth2, $this->request->getAttribute("page_url"), $this->persistenceDrivers, $this->daoLocator);
		}
	}
	
	private function authorize() {
		$xml = $this->application->getXML()->security->authorization;
		if(empty($xml)) throw new ServletApplicationException("Entry missing in configuration.xml: security.authentication");
		
		$userID = null;
		foreach($this->persistenceDrivers as $persistenceDriver) {
			$userID = $persistenceDriver->load();
		}

		if($xml->by_route) {
			require_once("application/models/XMLAuthorizationWrapper.php");
			new XMLAuthorizationWrapper($this->application->getXML(), $this->request->getAttribute("page_url"), $userID);
		}
		if($xml->by_dao) {
			require_once("application/models/DAOAuthorizationWrapper.php");
			new DAOAuthorizationWrapper($xml->oauth2, $this->request->getAttribute("page_url"), $userID, $this->daoLocator);
		}
	}
}