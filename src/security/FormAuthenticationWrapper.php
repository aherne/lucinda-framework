<?php
require_once("AuthenticationWrapper.php");
/**
 * Binds FormAuthentication @ SECURITY-API to settings from configuration.xml @ SERVLETS-API then performs login/logout if it matches paths @ xml via database.
 */
class FormAuthenticationWrapper extends AuthenticationWrapper {
	const DEFAULT_PARAMETER_USERNAME = "username";
	const DEFAULT_PARAMETER_PASSWORD = "password";
	const DEFAULT_PARAMETER_REMEMBER_ME = "remember_me";
	const DEFAULT_TARGET_PAGE = "index";
	const DEFAULT_LOGIN_PAGE = "login";
	const DEFAULT_LOGOUT_PAGE = "logout";

	private $xml;
	private $currentPage;
	private $authentication;

	/**
	 * Creates an object.
	 * 
	 * @param SimpleXMLElement $xml Contents of security.authentication.form tag @ configuration.xml.
	 * @param string $currentPage Current page requested.
	 * @param PersistenceDriver[] $persistenceDrivers List of drivers to persist information across requests.
	 * @param DAOLocator $daoLocator Utility that locates DAO classes referenced in XML.
	 * @param CsrfTokenWrapper $csrf Object that performs CSRF token checks.
	 * @throws ApplicationException If XML is malformed.
	 * @throws AuthenticationException If one or more persistence drivers are not instanceof PersistenceDriver
	 * @throws TokenException If CSRF checks fail
	 * @throws SQLConnectionException If connection to database server fails.
	 * @throws SQLStatementException If query to database server fails.
	 */
	public function __construct(SimpleXMLElement $xml, $currentPage, $persistenceDrivers, DAOLocator $daoLocator, CsrfTokenWrapper $csrf) {
		if(!$csrf) throw new ApplicationException("security.csrf tag is missing!");
		$this->xml = $xml;
		$this->currentPage = $currentPage;
		$this->authentication = new FormAuthentication($daoLocator->locate($xml, "dao", "UserAuthenticationDAO"), $persistenceDrivers);

		// checks if a login action was requested, in which case it forwards 
		$sourcePage = (string) $this->xml->login["page"];
		if(!$sourcePage) $sourcePage = self::DEFAULT_LOGIN_PAGE;
		if($sourcePage == $this->currentPage && !empty($_POST)) {
			$this->login($sourcePage, $csrf);
		}
		
		// checks if a logout action was requested, in which case it forwards
		$sourcePage = (string) $this->xml->logout["page"];
		if(!$sourcePage) $sourcePage = self::DEFAULT_LOGOUT_PAGE;
		if($sourcePage == $this->currentPage) {
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
		
		// login
		$targetPage = (string) $this->xml->login["target"];
		if(!$targetPage) $targetPage = self::DEFAULT_TARGET_PAGE;
		$parameterUsername = (string) $this->xml->login["parameter_username"];
		$parameterPassword = (string) $this->xml->login["parameter_password"];
		$parameterRememberMe = (string) $this->xml->login["parameter_rememberMe"];
		
		// set result
		$result = $this->authentication->login(
				($parameterUsername?$parameterUsername:self::DEFAULT_PARAMETER_USERNAME),
				($parameterPassword?$parameterPassword:self::DEFAULT_PARAMETER_PASSWORD),
				($parameterRememberMe?$parameterRememberMe:self::DEFAULT_PARAMETER_REMEMBER_ME)
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