<?php
require_once("application/models/DAOLocator.php");
require_once("libraries/php-security-api/loader.php");

/**
 * Reads XML "security" tag for settings that define how authentication/authorization/persistence will be performed inside your application.
 * NOTE: this is not needed if your application serves only public content!
 */
class SecurityListener extends RequestListener {
	private $daoLocator;
	private $persistenceDrivers = array();
	
	public function run() {
		$this->setDAOLocator();
		$this->setPersistenceDrivers();
		$this->setUserID();

		$this->csrf();
		$this->authenticate();
		$this->authorize();
	}

	/**
	 * Starts DAO locator helper
	 */
	private function setDAOLocator() {
		$this->daoLocator = new DAOLocator($this->application->getXML());
	}

	/**
	 * Detects drivers where user unique identifier will be persisted to across requests based on contents of security.persistence tag.
	 * 
	 * Supported drivers:
	 * 1. session: user id will persist as a session parameter 
	 * 2. remember_me: user id will persist as a crypted cookie
	 * 3. token: user id will become available 
	 * @throws ServletApplicationException When settings were improperly written.
	 */
	private function setPersistenceDrivers() {
		$xml = $this->application->getXML()->security->persistence;
		if(empty($xml)) return; // it is allowed for elements to not persist

		if($xml->session) {
			require_once("application/models/persistence/SessionPersistenceDriverWrapper.php");
			$wrapper = new SessionPersistenceDriverWrapper($xml->session);
			$this->persistenceDrivers[] = $wrapper->getDriver();
		}

		if($xml->remember_me) {
			require_once("application/models/persistence/RememberMePersistenceDriverWrapper.php");
			$wrapper = new RememberMePersistenceDriverWrapper($xml->remember_me);
			$this->persistenceDrivers[] = $wrapper->getDriver();
		}

		if($xml->token) {
			require_once("application/models/persistence/TokenPersistenceDriverWrapper.php");
			$wrapper = new TokenPersistenceDriverWrapper($xml->token);
			$this->persistenceDrivers[] = $wrapper->getDriver();
		}
	}

	/**
	 * Detects user unique identifier from persistence drivers and saves it to "user_id" request attribute.
	 */
	private function setUserID() {
		$userID = null;
		foreach($this->persistenceDrivers as $persistenceDriver) {
			$userID = $persistenceDriver->load();
			if($userID) {
				break;
			}
		}
		$this->request->setAttribute("user_id", $userID);
	}

	private function csrf() {

	}

	/**
	 * Performs authentication based on drivers defined in security.authentication tag.
	 * 
	 * Supported drivers:
	 * - form: user will be authenticated based on a login form
	 * - oauth2: user will be authenticated based on an oauth2 driver (supported: google, facebook)
	 * 
	 * @throws ServletApplicationException If XML settings are incorrect
	 */
	private function authenticate() {
		$xml = $this->application->getXML()->security->authentication;
		if(empty($xml)) throw new ServletApplicationException("Entry missing in configuration.xml: security.authentication");

		if($xml->form) {
			require_once("application/models/authentication/FormAuthenticationWrapper.php");
			new FormAuthenticationWrapper($xml->form, $this->request->getAttribute("page_url"), $this->persistenceDrivers, $this->daoLocator);
		}
		if($xml->oauth2) {
			require_once("application/models/authentication/Oauth2AuthenticationWrapper.php");
			new Oauth2AuthenticationWrapper($xml->oauth2, $this->request->getAttribute("page_url"), $this->persistenceDrivers, $this->daoLocator);
		}
	}

	/**
	 * Performs authorization of user to resource based on drivers defined in security.authorization tag.
	 * 
	 * Supported drivers:
	 * - by_route: user will be authorized based on logged in status and "access" property of routes.route tags set in XML (can be ROLE_USER / ROLE_GUEST) 
	 * - by_dao: user will be authorized based on logged in user and page accessed via mandatory user-defined DAOs (UserAuthorizationDAO, PageAuthorizationDAO)
	 * 
	 * @throws ServletApplicationException If XML settings are incorrect
	 */
	private function authorize() {
		$xml = $this->application->getXML()->security->authorization;
		if(empty($xml)) throw new ServletApplicationException("Entry missing in configuration.xml: security.authentication");

		if($xml->by_route) {
			require_once("application/models/authorization/XMLAuthorizationWrapper.php");
			new XMLAuthorizationWrapper($this->application->getXML(), $this->request->getAttribute("page_url"), $this->request->getAttribute("user_id"));
		}
		if($xml->by_dao) {
			require_once("application/models/authorization/DAOAuthorizationWrapper.php");
			new DAOAuthorizationWrapper($xml->by_dao, $this->request->getAttribute("page_url"), $this->request->getAttribute("user_id"), $this->daoLocator);
		}
	}
}