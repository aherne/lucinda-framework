<?php
require_once("libraries/php-security-api/src/authentication/FormAuthentication.php");
/**
 * 
 * <form dao="{CLASS_PATH}">
				<login parameter_username="" parameter_password="" page="{LOGIN_URL}" target="{PAGE_AFTER_LOGIN}" parameter_rememberMe=""/>
				<logout page="{LOGOUT_URL}" target="{PAGE_AFTER_LOGOUT}"/>
			</form>
 * @author aherne
 *
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
	
	public function __construct($xml, $currentPage, $persistenceDrivers, DAOLocator $daoLocator) {		
		$this->xml = $xml;
		$this->currentPage = $currentPage;
		$this->authentication = new FormAuthentication($daoLocator->locate($xml, "dao", "UserAuthenticationDAO"), $persistenceDrivers);
			
		$this->login();
		$this->logout();
	}
	
	private function login() {
		$sourcePage = (string) $this->xml->login["page"];
		if(!$sourcePage) $sourcePage = self::DEFAULT_LOGIN_PAGE;
		if($sourcePage == $this->currentPage && !empty($_POST)) {
			$targetPage = (string) $this->xml->login["target"];
			if(!$targetPage) $targetPage = self::DEFAULT_TARGET_PAGE;
			$parameterUsername = (string) $this->xml->login["parameter_username"];
			$parameterPassword = (string) $this->xml->login["parameter_password"];
			$parameterRememberMe = (string) $this->xml->login["parameter_rememberMe"];
			try {
				$this->authentication->login(
						($parameterUsername?$parameterUsername:self::DEFAULT_PARAMETER_USERNAME),
						($parameterPassword?$parameterPassword:self::DEFAULT_PARAMETER_PASSWORD),
						($parameterRememberMe?$parameterRememberMe:self::DEFAULT_PARAMETER_REMEMBER_ME)
						);
				header("Location: ".$targetPage."?status=LOGIN_SUCCESS");
				exit();
			} catch (AuthenticationException $e) {
				header("Location: ".$sourcePage."?status=LOGIN_FAILED&message=".$e->getMessage());
				exit();
			}
		}
	}
	
	private function logout() {
		$sourcePage = (string) $this->xml->logout["page"];
		if(!$sourcePage) $sourcePage = self::DEFAULT_LOGOUT_PAGE;
		if($sourcePage == $this->currentPage) {
			$targetPage = (string) $this->xml->logout["target"];
			if(!$targetPage) $targetPage = self::DEFAULT_LOGIN_PAGE;
			try {
				$this->authentication->logout();
				header("Location: ".$targetPage."?status=LOGOUT_SUCCESS");
				exit();
			} catch (AuthenticationException $e) {
				header("Location: ".$targetPage."?status=LOGOUT_FAILED&message=".$e->getMessage());
				exit();
			}
		}
	}
}