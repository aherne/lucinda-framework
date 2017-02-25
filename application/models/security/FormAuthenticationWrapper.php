<?php
/**
 * Binds FormAuthentication @ SECURITY-API to settings from configuration.xml @ SERVLETS-API then performs login/logout if it matches paths @ xml.
 */
class FormAuthenticationWrapper {
	const DEFAULT_PARAMETER_USERNAME = "username";
	const DEFAULT_PARAMETER_PASSWORD = "password";
	const DEFAULT_PARAMETER_REMEMBER_ME = "remember_me";
	const DEFAULT_TARGET_PAGE = "index";
	const DEFAULT_LOGIN_PAGE = "login";
	const DEFAULT_LOGOUT_PAGE = "logout";

	private $xml;
	private $currentPage;
	private $authentication;
	
	private $result;

	public function __construct($xml, $currentPage, $persistenceDrivers, DAOLocator $daoLocator, CsrfTokenWrapper $csrf) {
		if(!$csrf) throw new ApplicationException("secury.csrf tag is missing!");
		$this->xml = $xml;
		$this->currentPage = $currentPage;
		$this->authentication = new FormAuthentication($daoLocator->locate($xml, "dao", "UserAuthenticationDAO"), $persistenceDrivers);
			
		$this->login($csrf);
		$this->logout();
	}

	/**
	 * Attempts to login when path requested matches login path @ XML and post parameters are sent. 
	 * 
	 * @param CsrfTokenWrapper $csrf Holder of csrf token used in cross-validating login request.
	 * @throws TokenException If csrf token is missing or invalid
	 * @throws AuthenticationException If parameters for username and password were missing or empty.
	 */
	private function login(CsrfTokenWrapper $csrf) {
		$sourcePage = (string) $this->xml->login["page"];
		if(!$sourcePage) $sourcePage = self::DEFAULT_LOGIN_PAGE;
		if($sourcePage == $this->currentPage && !empty($_POST)) {
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
	}

	/**
	 * Attempts to logout when path requested matches logout path @ XML
	 */
	private function logout() {
		$sourcePage = (string) $this->xml->logout["page"];
		if(!$sourcePage) $sourcePage = self::DEFAULT_LOGOUT_PAGE;
		if($sourcePage == $this->currentPage) {
			$targetPage = (string) $this->xml->logout["target"];
			if(!$targetPage) $targetPage = self::DEFAULT_LOGIN_PAGE;
			
			// set result
			$result = $this->authentication->logout();
			$this->setResult($result, $targetPage, $targetPage);
		}
	}
	
	/**
	 * Sets authentication result.
	 * 
	 * @param AuthenticationResult $result Holds a reference to an object that encapsulates authentication result.
	 * @param string $sourcePage Callback path to redirect to on failure.
	 * @param string $targetPage Callback path to redirect to on success.
	 */
	private function setResult(AuthenticationResult $result, $sourcePage, $targetPage) {
		if($result->getStatus()==AuthenticationResultStatus::OK) {
			$result->setCallbackURI($targetPage);
		} else {
			$result->setCallbackURI($sourcePage);
		}
		$this->result = $result;
	}
	
	/**
	 * Gets authentication result.
	 * 
	 * @return AuthenticationResult
	 */
	public function getResult() {
		return $this->result;
	}
}