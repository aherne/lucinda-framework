<?php
require_once("AuthenticationWrapper.php");

/**
 * Encapsulates login request data
 */
class LoginRequest {
	private $sourcePage;
	private $targetPage;
	private $username;
	private $password;
	private $rememberMe;
	
	/**
	 * Sets value of user name sent in login attempt.
	 * 
	 * @param string $username
	 */
	public function setUsername($username) {
		$this->username = $username;
	}
	
	/**
	 * Sets value of user password sent in login attempt.
	 *
	 * @param string $password
	 */
	public function setPassword($password) {
		$this->password= $password;
	}
	
	/**
	 * Sets value of remember me option sent in login attempt (or null, if application doesn't support remember me)
	 *
	 * @param boolean $rememberMe
	 */
	public function setRememberMe($rememberMe) {
		$this->rememberMe= $rememberMe;
	}
	
	/**
	 * Sets current page.
	 *
	 * @param string $sourcePage
	 */
	public function setSourcePage($sourcePage) {
		$this->sourcePage= $sourcePage;
	}
	
	/**
	 * Sets page to redirect to on login/logout success/failure.
	 *
	 * @param string $targetPage
	 */
	public function setDestinationPage($targetPage) {
		$this->targetPage= $targetPage;
	}
	
	/**
	 * Gets value of user name sent in login attempt.
	 *
	 * @return string
	 */
	public function getUsername() {
		return $this->username;
	}
		
	/**
	 * Gets value of user password sent in login attempt.
	 *
	 * @return string
	 */
	public function getPassword() {
		return $this->password;
	}
		
	/**
	 * Gets value of remember me option sent in login attempt (or null, if application doesn't support remember me)
	 *
	 * @return boolean|null
	 */
	public function getRememberMe() {
		return $this->rememberMe;
	}
	
	/**
	 * Gets current page.
	 *
	 * @return string
	 */
	public function getSourcePage() {
		return $this->sourcePage;
	}
	
	/**
	 * Gets page to redirect to on login/logout success/failure.
	 *
	 * @return string
	 */
	public function getDestinationPage() {
		return $this->targetPage;
	}
}

/**
 * Encapsulates logout request data
 */
class LogoutRequest {
	private $sourcePage;
	private $targetPage;
	
	/**
	 * Sets current page.
	 *
	 * @param string $sourcePage
	 */
	public function setSourcePage($sourcePage) {
		$this->sourcePage= $sourcePage;
	}
	
	/**
	 * Sets page to redirect to on login/logout success/failure.
	 *
	 * @param string $targetPage
	 */
	public function setDestinationPage($targetPage) {
		$this->targetPage= $targetPage;
	}
	
	/**
	 * Gets current page.
	 *
	 * @return string
	 */
	public function getSourcePage() {
		return $this->sourcePage;
	}
	
	/**
	 * Gets page to redirect to on login/logout success/failure.
	 *
	 * @return string
	 */
	public function getDestinationPage() {
		return $this->targetPage;
	}
}


/**
 * Validates authentication requests in configuration.xml and encapsulates them into objects
 */
class FormRequestValidator {
	const DEFAULT_PARAMETER_USERNAME = "username";
	const DEFAULT_PARAMETER_PASSWORD = "password";
	const DEFAULT_PARAMETER_REMEMBER_ME = "remember_me";
	const DEFAULT_TARGET_PAGE = "index";
	const DEFAULT_LOGIN_PAGE = "login";
	const DEFAULT_LOGOUT_PAGE = "logout";
	
	private $xml;
	
	/**
	 * Creates an object.
	 *
	 * @param SimpleXMLElement $xml Contents of security.authentication.form tag @ configuration.xml.
	 * @throws ApplicationException If XML is malformed.
	 */
	public function __construct(SimpleXMLElement $xml) {
		$this->xml = $xml->security->authentication->form;
	}
	
	/**
	 * Sets up login data, if operation was requested
	 * 
	 * @throws AuthenticationException If XML/request is malformed.
	 * @param string $currentPage Current page requested.
	 * @return LoginRequest|null
	 */
	public function login($currentPage) {
		$loginRequest = new LoginRequest();
		
		// set source page;
		$sourcePage = (string) $this->xml->login["page"];
		if(!$sourcePage) $sourcePage = self::DEFAULT_LOGIN_PAGE;
		if($sourcePage != $currentPage || empty($_POST)) {
			return null;
		}
		$loginRequest->setSourcePage($sourcePage);		
		
		// get target page
		$targetPage = (string) $this->xml->login["target"];
		if(!$targetPage) $targetPage = self::DEFAULT_TARGET_PAGE;
		$loginRequest->setDestinationPage($targetPage);
		
		// get parameter names
		$parameterUsername = (string) $this->xml->login["parameter_username"];
		if(!$parameterUsername) throw new AuthenticationException("XML parameter missing: parameter_username");
		$parameterPassword = (string) $this->xml->login["parameter_password"];
		if(!$parameterUsername) throw new AuthenticationException("XML parameter missing: parameter_password");
		$parameterRememberMe = (string) $this->xml->login["parameter_rememberMe"];
		
		// get parameter values
		$username = (!empty($_POST[$parameterUsername])?$_POST[$parameterUsername]:"");
		if(!$username) throw new AuthenticationException("POST parameter missing: ".$parameterUsername);
		$loginRequest->setUsername($username);
		
		$password = (!empty($_POST[$parameterPassword])?$_POST[$parameterPassword]:"");
		if(!$password) throw new AuthenticationException("POST parameter missing: ".$parameterPassword);
		$loginRequest->setPassword($password);
		
		if($parameterRememberMe) {
			$loginRequest->setRememberMe(!empty($_POST[$parameterRememberMe])?true:false);
		}
		
		return $loginRequest;
	}
	
	/**
	 * Sets up logout data, if operation was requested
	 *
	 * @throws ApplicationException If XML is malformed.
	 * @return LogoutRequest|null
	 */
	public function logout($currentPage) {
		$logoutRequest = new LogoutRequest();
		
		// set source page
		$sourcePage = (string) $this->xml->logout["page"];
		if(!$sourcePage) $sourcePage = self::DEFAULT_LOGOUT_PAGE;
		if($sourcePage != $currentPage) {
			return null;
		}
		$logoutRequest->setSourcePage($currentPage);
		
		// set destination page
		$targetPage = (string) $this->xml->logout["target"];
		if(!$targetPage) $targetPage = self::DEFAULT_LOGIN_PAGE;
		$logoutRequest->setDestinationPage($targetPage);
		
		return $logoutRequest;
	}
}