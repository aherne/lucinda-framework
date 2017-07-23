<?php
require_once("AuthenticationWrapper.php");
/**
 * Binds DAOAuthentication @ SECURITY-API to settings from configuration.xml @ SERVLETS-API then performs login/logout if it matches paths @ xml via database.
 */
class DAOAuthenticationWrapper extends AuthenticationWrapper {
	const DEFAULT_PARAMETER_USERNAME = "username";
	const DEFAULT_PARAMETER_PASSWORD = "password";
	const DEFAULT_PARAMETER_REMEMBER_ME = "remember_me";
	const DEFAULT_TARGET_PAGE = "index";
	const DEFAULT_LOGIN_PAGE = "login";
	const DEFAULT_LOGOUT_PAGE = "logout";

	private $xml;
	private $authentication;

	/**
	 * Creates an object.
	 * 
	 * @param SimpleXMLElement $xml Contents of security.authentication.form tag @ configuration.xml.
	 * @param string $currentPage Current page requested.
	 * @param PersistenceDriver[] $persistenceDrivers List of drivers to persist information across requests.
	 * @param CsrfTokenWrapper $csrf Object that performs CSRF token checks.
	 * @throws ApplicationException If XML is malformed.
	 * @throws AuthenticationException If one or more persistence drivers are not instanceof PersistenceDriver
	 * @throws TokenException If CSRF checks fail
	 * @throws SQLConnectionException If connection to database server fails.
	 * @throws SQLStatementException If query to database server fails.
	 */
	public function __construct(SimpleXMLElement $xml, $currentPage, $persistenceDrivers, CsrfTokenWrapper $csrf) {
		// create dao object
		$locator = new DAOLocator($xml);
		$daoObject = $locator->locate($xml->security->authentication->form, "dao", "UserAuthenticationDAO");

		// setup class properties
		$this->xml = $xml->security->authentication->form;
		$this->authentication = new DAOAuthentication($daoObject, $persistenceDrivers);

		// checks if a login action was requested, in which case it forwards 
		$sourcePage = (string) $this->xml->login["page"];
		if(!$sourcePage) $sourcePage = self::DEFAULT_LOGIN_PAGE;
		if($sourcePage == $currentPage && !empty($_POST)) {
			$this->login($sourcePage, $csrf);
		}
		
		// checks if a logout action was requested, in which case it forwards
		$sourcePage = (string) $this->xml->logout["page"];
		if(!$sourcePage) $sourcePage = self::DEFAULT_LOGOUT_PAGE;
		if($sourcePage == $currentPage) {
			$this->logout();
		}
	}

	/**
	 * Logs user in.
	 * 
	 * @param string $sourcePage Current page requested.
	 * @param CsrfTokenWrapper $csrf Performs CSRF checks on mandatory $_POST["csrf"] parameter.
	 * @throws TokenException If CSRF checks fail
	 * @throws SQLConnectionException If connection to database server fails.
	 * @throws SQLStatementException If query to database server fails.
	 */
	private function login($sourcePage, CsrfTokenWrapper $csrf) {
		// check csrf token
		if(empty($_POST['csrf']) || !$csrf->isValid($_POST['csrf'], 0)) {
			throw new TokenException("CSRF token is invalid or missing!");
		}
		
		// get target page
		$targetPage = (string) $this->xml->login["target"];
		if(!$targetPage) $targetPage = self::DEFAULT_TARGET_PAGE;
		
		// get parameter names
		$parameterUsername = (string) $this->xml->login["parameter_username"];
		if(!$parameterUsername) throw new AuthenticationException("XML parameter missing: parameter_username");
		$parameterPassword = (string) $this->xml->login["parameter_password"];
		if(!$parameterUsername) throw new AuthenticationException("XML parameter missing: parameter_password");
		$parameterRememberMe = (string) $this->xml->login["parameter_rememberMe"];
		
		// get parameter values
		$username = (!empty($_POST[$parameterUsername])?$_POST[$parameterUsername]:"");
		if(!$username) throw new AuthenticationException("POST parameter missing: ".$parameterUsername);
		$password = (!empty($_POST[$parameterPassword])?$_POST[$parameterPassword]:"");
		if(!$password) throw new AuthenticationException("POST parameter missing: ".$parameterPassword);
		$rememberMe = ($parameterRememberMe?(!empty($_POST[$parameterRememberMe])?(boolean) $_POST[$parameterRememberMe]:false):null);
		
		// set result
		$result = $this->authentication->login(
				$username,
				$password,
				$rememberMe
				);
		$this->setResult($result, $sourcePage, $targetPage);
	}

	/**
	 * Logs user out.
	 * 
	 * @throws SQLConnectionException If connection to database server fails.
	 * @throws SQLStatementException If query to database server fails.
	 */
	private function logout() {
		$targetPage = (string) $this->xml->logout["target"];
		if(!$targetPage) $targetPage = self::DEFAULT_LOGIN_PAGE;
		
		// set result
		$result = $this->authentication->logout();
		$this->setResult($result, $targetPage, $targetPage);
	}
}