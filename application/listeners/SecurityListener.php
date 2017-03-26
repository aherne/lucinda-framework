<?php
require_once("src/security/DAOLocator.php");
require_once("libraries/php-security-api/loader.php");
require_once("application/models/AuthenticationRenderer.php");
require_once("application/models/AuthorizationRenderer.php");

/**
 * Connects PHP-SECURITY-API and OAUTH2-CLIENT with CONFIGURATION.XML @ SERVLETS API.
 * Reads XML "security" tag for sub-tags that define web security related settings then forwards latter to wrappers that link XML tags/properties with 
 * Security and OAuth2 API constructs. Above mentioned sub-tags are:
 * - csrf: (MANDATORY) this tag holds settings necessary to generate a CSRF token
 * - authentication: (MANDATORY) this tag holds sub-tags that set up your authentication solution chosen
 * - authorization; (MANDATORY) this tag holds sub-tags that set up your authorization solution chosen
 * - persistence: (OPTIONAL) this tag holds sub-tags that set up your state persistence driver chosen
 * 
 * Syntax for XML "security" tag is:
 * <security>
 * 		<authentication>...</authentication>
 * 		<persistence>...</persistence>
 * 		<authorization ...>...</authorization>
 * 		<csrf />
 * </security>
 * NOTE: this listener is not needed if your application serves only public content!
 */
class SecurityListener extends RequestListener {
	private $persistenceDrivers = array();
	
	public function run() {
		$this->setPersistenceDrivers();
		$this->setUserID();

		$this->setCsrfToken();
		$this->authenticate();
		$this->authorize();
	}

	/**
	 * Detects drivers where user unique identifier will be persisted to across requests based on contents of security.persistence tag. Supported drivers:
	 * 1. session: user id will persist as a session parameter 
	 * 2. remember_me: user id will persist as a crypted cookie
	 * 3. token: user id will become available 
	 * 
	 * Syntax for XML "persistence" tag is:
	 * <persistence>
	 * 		<session .../>
	 * 		<token .../>
	 * 		<remember-me .../>
	 * </persistence>
	 * @throws ApplicationException When settings were improperly written.
	 */
	private function setPersistenceDrivers() {
		$xml = $this->application->getXML()->security->persistence;
		if(empty($xml)) return; // it is allowed for elements to not persist

		if($xml->session) {
			require_once("src/security/SessionPersistenceDriverWrapper.php");
			$wrapper = new SessionPersistenceDriverWrapper($xml->session);
			$this->persistenceDrivers[] = $wrapper->getDriver();
		}

		if($xml->remember_me) {
			require_once("src/security/RememberMePersistenceDriverWrapper.php");
			$wrapper = new RememberMePersistenceDriverWrapper($xml->remember_me);
			$this->persistenceDrivers[] = $wrapper->getDriver();
		}

		if($xml->token) {
			require_once("src/security/TokenPersistenceDriverWrapper.php");
			$wrapper = new TokenPersistenceDriverWrapper($xml->token);
			$this->persistenceDrivers[] = $wrapper->getDriver();
		}
	}

	/**
	 * Detects user unique identifier based on persistence drivers and saves it to "user_id" request attribute.
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
	
	/**
	 * Sets CSRF token to use in re-authenticating while performing critical operations (such as login) based on contents of security.csrf tag.
	 * 
	 * Syntax for XML "csrf" tag is:
	 * <csrf .../>
	 * 
	 * @throws ApplicationException If XML settings are incorrect
	 */
	private function setCsrfToken() {
		$xml = $this->application->getXML()->security->csrf;
		if(empty($xml)) {
			throw new ApplicationException("Entry missing in configuration.xml: security.csrf");
		}
		
		require_once("src/security/CsrfTokenWrapper.php");
		$this->request->setAttribute("csrf", new CsrfTokenWrapper($xml));
	}

	/**
	 * Performs authentication based on drivers defined in security.authentication tag. Supported drivers:
	 * - form: user will be authenticated based on a login form
	 * - oauth2: user will be authenticated based on an oauth2 driver (supported: google, facebook)
	 * 
	 * Syntax for XML "authentication" tag is:
	 * <authentication>
	 * 		<form ...> ... </form>
	 * 		<oauth2 ...> ... </oauth2>
	 * </authentication>
	 * 
	 * @throws ApplicationException If XML settings are incorrect
	 */
	private function authenticate() {
		$xml = $this->application->getXML()->security->authentication;
		if(empty($xml)) {
			throw new ApplicationException("Entry missing in configuration.xml: security.authentication");
		}

		$wrapper = null;
		if($xml->form) {
			require_once("src/security/FormAuthenticationWrapper.php");
			$wrapper = new FormAuthenticationWrapper(
					$this->application->getXML(), 
					$this->request->getAttribute("page_url"), 
					$this->persistenceDrivers, 
					$this->request->getAttribute("csrf"));
		}
		if($xml->oauth2) {
			require_once("src/security/authentication/Oauth2AuthenticationWrapper.php");
			$wrapper = new Oauth2AuthenticationWrapper(
					$this->application->getXML(), 
					$this->request->getAttribute("page_url"), 
					$this->persistenceDrivers, 
					$this->request->getAttribute("csrf"));
		}
		if($wrapper) {
			if(!$wrapper->getResult()) {
				// no authentication was requested
				return;
			} else {
				// authentication was requested
				new AuthenticationRenderer($wrapper->getResult(), $this->request->getAttribute("page_content_type"), $this->request->getURI()->getContextPath(), $this->persistenceDrivers);
			}
		} else {
			throw new ApplicationException("No authentication driver found in configuration.xml: security.authentication");
		}
	}

	/**
	 * Performs authorization of user to resource based on drivers defined in security.authorization tag. Supported drivers:
	 * - by_route: user will be authorized based on logged in status and "access" property of routes.route tags set in XML (can be ROLE_USER / ROLE_GUEST) 
	 * - by_dao: user will be authorized based on logged in user and page accessed via mandatory user-defined DAOs (UserAuthorizationDAO, PageAuthorizationDAO)
	 * 
	 * Syntax for XML "authorization" tag is:
	 * <authorization ...>
	 * 		<by_route/>
	 * 		<by_dao .../>
	 * </authorization>
	 * 
	 * @throws ApplicationException If XML settings are incorrect
	 */
	private function authorize() {
		$xml = $this->application->getXML()->security->authorization;
		if(empty($xml)) {
			throw new ApplicationException("Entry missing in configuration.xml: security.authentication");
		}

		$wrapper = null;
		if($xml->by_route) {
			require_once("src/security/authorization/XMLAuthorizationWrapper.php");
			$wrapper = new XMLAuthorizationWrapper(
					$this->application->getXML(), 
					$this->request->getAttribute("page_url"), 
					$this->request->getAttribute("user_id"));
			$wrapper->getResult();
		}
		if($xml->by_dao) {
			require_once("src/security/authorization/DAOAuthorizationWrapper.php");
			$wrapper = new DAOAuthorizationWrapper(
					$this->application->getXML(), 
					$this->request->getAttribute("page_url"), 
					$this->request->getAttribute("user_id"));
		}
		if($wrapper) {
			if($wrapper->getResult()->getStatus() == AuthorizationResultStatus::OK) {
				// authorization was successful
				return; 
			} else {
				// authorization failed
				new AuthorizationRenderer($wrapper->getResult(), $this->request->getAttribute("page_content_type"));
			}			
		} else {
			throw new ApplicationException("No authorization driver found in configuration.xml: security.authentication");
		}
	}
}