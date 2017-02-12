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
	
	private $authentication;
	
	public function __construct($xml, $currentPage, $persistenceDrivers) {		
		$this->authentication = new FormAuthentication($this->getDAO($xml), $persistenceDrivers);
			
		// login page
		$sourcePage = (string) $xml->login["page"];
		if(!$sourcePage) $sourcePage = self::DEFAULT_LOGIN_PAGE;
		if($sourcePage == $currentPage && !empty($_POST)) {
			$this->login($xml->login, $sourcePage);
		}
			
		// logout page
		$sourcePage = (string) $xml->logout["page"];
		if(!$sourcePage) $sourcePage = self::DEFAULT_LOGOUT_PAGE;
		if($sourcePage == $currentPage) {
			$this->logout($xml->logout, $sourcePage);
		}
	}
	
	private function login(SimpleXMLElement $xml, $sourcePage) {
		$targetPage = (string) $xml["target"];
		if(!$targetPage) throw new ServletApplicationException("'target' attribute of 'login' tag is missing/empty!");
		$parameterUsername = (string) $xml["parameter_username"];
		$parameterPassword = (string) $xml["parameter_password"];
		$parameterRememberMe = (string) $xml["parameter_rememberMe"];
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
	
	private function logout(SimpleXMLElement $xml, $sourcePage) {
		$targetPage = (string) $xml["target"];
		if(!$targetPage) $targetPage = self::DEFAULT_TARGET_PAGE;
		try {
			$this->authentication->logout();
			header("Location: ".$targetPage."?status=LOGOUT_SUCCESS");
			exit();
		} catch (AuthenticationException $e) {
			header("Location: ".$targetPage."?status=LOGOUT_FAILED&message=".$e->getMessage());
			exit();
		}
	}
	
	private function getDAO(SimpleXMLElement $xml) {
		$dao = (string) $xml["dao"];
		if(!$dao) throw new ServletApplicationException("'dao' attribute of 'form' tag is missing!");
		
		// load file
		$daoFile = $dao.".php";
		if(!file_exists($daoFile)) throw new ServletApplicationException("DAO file not found: ".$daoFile."!");
		require_once($daoFile);
		
		// locate class
		$daoClass = substr($dao,strrpos($dao,"/")+1);
		if(!($daoClass instanceof UserAuthenticationDAO)) throw new ServletApplicationException("DAO class must be instance of UserAuthenticationDAO!");
		return new $daoClass();
	}
}