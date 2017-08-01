<?php
require_once("AuthenticationWrapper.php");
require_once("FormRequestValidator.php");

/**
 * Binds DAOAuthentication @ SECURITY-API to settings from configuration.xml @ SERVLETS-API then performs login/logout if it matches paths @ xml via database.
 */
class DAOAuthenticationWrapper extends AuthenticationWrapper {
	private $driver;

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
		// set driver
		$locator = new DAOLocator($xml);
		$daoObject = $locator->locate($xml->security->authentication->form, "dao", "UserAuthenticationDAO");
		$this->driver = new DAOAuthentication($daoObject, $persistenceDrivers);

		// setup class properties
		$validator = new FormRequestValidator($xml);
		
		// checks if a login action was requested, in which case it forwards object to driver
		if($request = $validator->login($currentPage)) {
			// check csrf token
			if(empty($_POST['csrf']) || !$csrf->isValid($_POST['csrf'], 0)) {
				throw new TokenException("CSRF token is invalid or missing!");
			}
			$this->login($request);
		}
		
		// checks if a logout action was requested, in which case it forwards object to driver
		if($request = $validator->logout($currentPage)) {
			$this->logout($request);
		}
	}

	/**
	 * Logs user in authentication driver.
	 * 
	 * @param LoginRequest $request Encapsulates login request data.
	 * @throws SQLConnectionException If connection to database server fails.
	 * @throws SQLStatementException If query to database server fails.
	 */
	private function login(LoginRequest $request) {		
		// set result
		$result = $this->driver->login(
				$request->getUsername(),
				$request->getPassword(),
				$request->getRememberMe()
				);
		$this->setResult($result, $request->getSourcePage(), $request->getDestinationPage());
	}

	/**
	 * Logs user out authentication driver.
	 * 
	 * @param LogoutRequest $request Encapsulates logout request data.
	 * @throws SQLConnectionException If connection to database server fails.
	 * @throws SQLStatementException If query to database server fails.
	 */
	private function logout(LogoutRequest $request) {
		// set result
		$result = $this->driver->logout();
		$this->setResult($result, $request->getDestinationPage(), $request->getDestinationPage());
	}
}