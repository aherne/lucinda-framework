<?php
require_once("libraries/oauth2client/loader.php");
require_once("oauth2/Oauth2Authentication.php");
/**
 * 
			<oauth2 dao="{CLASS_PATH}" auto_create="1" logout="{LOGOUT_URL}" target="{PAGE_AFTER_LOGIN}" login="{PAGE_AFTER_LOGIN}">
				<driver name="facebook" callback="{LOGIN_URL}">
					<client_id>{APPLICATION_ID}</client_id>
					<client_secret>{APPLICATION_SECRET}</client_secret>
				</driver>
				
			</oauth2>
 */
class Oauth2AuthenticationWrapper {
	const DEFAULT_CALLBACK_PAGE = "{DRIVER}/login";
	const DEFAULT_LOGIN_PAGE = "login";
	const DEFAULT_LOGOUT_PAGE = "logout";
	const DEFAULT_TARGET_PAGE = "index";
	
	private $xml;
	private $currentPage;
	private $persistenceDrivers;
	private $authentication;
	
	public function __construct($xml, $currentPage, $persistenceDrivers, DAOLocator $locator) {
		$this->xml = $xml;
		$this->currentPage = $currentPage;
		$this->persistenceDrivers = $persistenceDrivers;
		$this->authentication = new Oauth2Authentication($locator->locate($xml, "dao", "UserOauth2AuthenticationDAO"), $persistenceDrivers);
		
		$this->login();
		$this->logout();
	}
	
	private function login() {
		$this->xmlLocal = $this->xml->driver;
		foreach($this->xmlLocal as $element) {
			$driverName = (string) $element["name"];
			if(!$driverName) throw new ApplicationException("Property 'name' of oauth2.driver tag is mandatory!");
				
			$callbackPage = (string) $element["callback"];
			if(!$callbackPage) $callbackPage = str_replace("{DRIVER}", $driverName, self::DEFAULT_CALLBACK_PAGE);
			if($callbackPage == $this->currentPage) {
				// detect class and load fileload file
				$driverClass = ucwords($driverName)."Login";
				$driverFilePath = "application/models/authentication/oauth2/".strtolower($driverName)."/".$driverClass.".php";
				if(!file_exists($driverFilePath)) throw new ApplicationException("Driver class not found: ".$driverFilePath);
				require_once($driverFilePath);
		
				// create object and login
				$targetSuccessPage = (string) $this->xml["target"];
				if(!$targetSuccessPage) $targetSuccessPage = self::DEFAULT_TARGET_PAGE;
				$targetFailurePage = (string) $this->xml["login"];
				if(!$targetFailurePage) $targetFailurePage = self::DEFAULT_LOGIN_PAGE;
				$clientInformation = $this->getClientInformation($element);
				$createIfNotExists = (integer) $this->xml["auto_create"];
				$authorizationCode = (!empty($_GET["code"])?$_GET["code"]:"");
				try {
					$this->authentication->login(new $driverClass($clientInformation), $authorizationCode, $createIfNotExists);
					header("Location: ".$targetSuccessPage."?status=LOGIN_SUCCESS");
					exit();
				} catch(OAuth2\ServerException $e) {
					header("Location: ".$targetFailurePage."?status=LOGIN_FAILED&message=".$e->getMessage());
					exit();
				}
			}
		}
	}
	
	private function logout() {
		$loginPage = (string) $this->xml["login"];
		if(!$loginPage) $loginPage = self::DEFAULT_LOGIN_PAGE;
		$logoutPage = (string) $this->xml["logout"];
		if(!$logoutPage) $logoutPage = self::DEFAULT_LOGOUT_PAGE;
		if($logoutPage == $this->currentPage) {
			try {
				$this->authentication->logout();
				header("Location: ".$loginPage."?status=LOGOUT_SUCCESS");
				exit();
			} catch(AuthenticationException $e) {
				header("Location: ".$loginPage."?status=LOGOUT_FAILED&message=".$e->getMessage());
				exit();
			}
		}
	}
	
	private function getClientInformation(SimpleXMLElement $xml) {
		// get client id and secret from xml
		$clientID = (string) $xml->client_id;
		$clientSecret = (string) $xml->client_secret;
		if(!$clientID || !$clientSecret) throw new ApplicationException("Tags 'client_id' and 'client_secret' are mandatory!");
		
		// callback page is same as driver login page
		$callbackPage = (isset($_SERVER['HTTPS'])?"https":"http")."://".$_SERVER['HTTP_HOST'].str_replace("?".$_SERVER["QUERY_STRING"],"",$_SERVER['REQUEST_URI']);
		
		return new OAuth2\ClientInformation($clientID, $clientSecret, $callbackPage);
	}
}