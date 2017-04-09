<?php
require_once("libraries/php-security-api/loader.php");
require_once("application/models/security/DAOLocator.php");

/**
 * Sets up and performs web security in your application by binding PHP-SECURITY-API & OAUTH2-CLIENT with contents of "security" tag @ CONFIGURATION.XML, 
 * itself handled by SERVLETS API. Syntax for XML "security" tag is:
 * <security>
 * 		<csrf />
 * 		<persistence>...</persistence>
 * 		<authentication>...</authentication>
 * 		<authorization ...>...</authorization>
 * </security>
 * 
 * Where:
 * - csrf: (MANDATORY) this tag holds settings necessary to generate a CSRF token
 * - persistence: (OPTIONAL) this tag holds sub-tags that set up your state persistence driver chosen
 * - authentication: (MANDATORY) this tag holds sub-tags that set up your authentication solution chosen
 * - authorization; (MANDATORY) this tag holds sub-tags that set up your authorization solution chosen
 * 
 * For security reasons, all authentication attempts require a CSRF token. Contents of "csrf" tag are used to setup an instance of CsrfTokenWrapper 
 * that is saved as "csrf" request attribute, to be used later on in generating or verifying csrf tokens.
 * 
 * The next stage is to find the state persistence method chosen (eg: session, cookie), based on contents of "persistence" tag. For each found stance, 
 * unique user id is searched for. If found, value is saved as "user_id" request attribute, to be used later on in the flow of application. For REST-ful or
 * many other web service applications where there should be no persistence, "persistence" tag is not required and authorization is done via a synchronizer token.
 * 
 * The next stage is investigating "authentication" tag. If authentication (login/logout) is requested and outcome is successful, "user_id" attribute is updated and:
 * - if page format is "html": client is redirected to logged in / logged out users' homepage with a status
 * - if page format is "json": client receives a json that contains a status and a synchronizer token (time and ip bound) to use in later authorization.
 * If authentication was unsuccessful:
 * - if page format is "html": client is redirected back to logged in / logged out page with error status
 * - if page format is "json": client receives a json that informs of failure via status
 * Otherwise, if no authentication was requested, request passes through.
 * 
 * The final stage is to investigating "authorization" tag. Client's right to requested resource is investigated depending on state (logged in or not) or 
 * discrete rights per resource (on many applications, not all logged in users have same rights to all protected resources).  
 * - if resource requested is not found, a HTTP 404 status code is invoked, then:
 * 		- if page format is "html": application/views/404.php is displayed
 * 		- if page format is "json": a json is returned with status = "not_found"
 * - otherwise
 * 		- if page is protected by login, but user is unauthenticated, a HTTP 401 Unauthorized status code is invoked then:
 * 			- if page format is "html": application/views/401.php is displayed
 * 			- if page format is "json": a json is returned with status = "unauthorized"
 * 		- if page is protected by login, but logged in user doesn't have rights to access it, a HTTP 403 Forbidden status code is invoked then:
 * 			- if page format is "html": application/views/403.php is displayed
 * 			- if page format is "json": a json is returned with status = "forbidden"
 * Otherwise, if resource is found and client has rights to access it, request passes through. 
 * 
 * @attribute csrf Stores an instance of CsrfTokenWrapper to use in generating tokens.
 * @attribute user_id Stores unique user identifier (for logged in users).
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

		require_once("application/models/security/CsrfTokenWrapper.php");
		$this->request->setAttribute("csrf", new CsrfTokenWrapper($xml));
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
			require_once("application/models/security/SessionPersistenceDriverWrapper.php");
			$wrapper = new SessionPersistenceDriverWrapper($xml->session);
			$this->persistenceDrivers[] = $wrapper->getDriver();
		}

		if($xml->remember_me) {
			require_once("application/models/security/RememberMePersistenceDriverWrapper.php");
			$wrapper = new RememberMePersistenceDriverWrapper($xml->remember_me);
			$this->persistenceDrivers[] = $wrapper->getDriver();
		}

		if($xml->token) {
			require_once("application/models/security/TokenPersistenceDriverWrapper.php");
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
			require_once("application/models/security/FormAuthenticationWrapper.php");
			$wrapper = new FormAuthenticationWrapper(
					$this->application->getXML(),
					$this->request->getAttribute("page_url"),
					$this->persistenceDrivers,
					$this->request->getAttribute("csrf"));
		}
		if($xml->oauth2) {
			require_once("application/models/security/Oauth2AuthenticationWrapper.php");
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
				require_once("application/models/security/AuthenticationRenderer.php");
				new AuthenticationRenderer($wrapper->getResult(), $this->request->getAttribute("page_content_type"), $this->request->getURI()->getContextPath(), $this->persistenceDrivers);
				// TODO: stop rendering here, use RedirectionException
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
			require_once("application/models/security/XMLAuthorizationWrapper.php");
			$wrapper = new XMLAuthorizationWrapper(
					$this->application->getXML(),
					$this->request->getAttribute("page_url"),
					$this->request->getAttribute("user_id"));
			$wrapper->getResult();
		}
		if($xml->by_dao) {
			require_once("application/models/security/DAOAuthorizationWrapper.php");
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
				require_once("application/models/security/AuthorizationRenderer.php");
				new AuthorizationRenderer($wrapper->getResult(), $this->request->getAttribute("page_content_type"), $this->request->getURI()->getContextPath());
			}
		} else {
			throw new ApplicationException("No authorization driver found in configuration.xml: security.authentication");
		}
	}
}