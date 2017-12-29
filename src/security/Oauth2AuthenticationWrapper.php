<?php
require_once("vendor/lucinda/oauth2-client/loader.php");
require_once("AuthenticationWrapper.php");

/**
 * Binds OAuth2Authentication @ SECURITY-API and Driver @ OAUTH2-CLIENT-API with settings from configuration.xml @ SERVLETS-API and vendor-specific 
 * (eg: google / facebook) driver implementation, then performs login/logout if path requested matches paths @ xml.
 */
class Oauth2AuthenticationWrapper extends AuthenticationWrapper {
	const DEFAULT_LOGIN_PAGE = "login";
	const DEFAULT_LOGOUT_PAGE = "logout";
	const DEFAULT_TARGET_PAGE = "index";
	
	private $xml;
	private $authentication;
	private $drivers = array();
	
	/**
	 * Creates an object
	 * 
	 * @param SimpleXMLElement $xml Contents of security.authentication.oauth2 tag @ configuration.xml.
	 * @param string $currentPage Current page requested.
	 * @param PersistenceDriver[] $persistenceDrivers List of drivers to persist information across requests.
	 * @param CsrfTokenWrapper $csrf Object that performs CSRF token checks.
	 * @throws ApplicationException If XML is malformed.
	 * @throws AuthenticationException If one or more persistence drivers are not instanceof PersistenceDriver
	 * @throws TokenException If CSRF checks fail.
	 * @throws SQLConnectionException If connection to database server fails.
	 * @throws SQLStatementException If query to database server fails.
	 * @throws OAuth2\ClientException When oauth2 local client sends malformed requests to oauth2 server.
	 * @throws OAuth2\ServerException When oauth2 remote server answers with an error.
	 */
	public function __construct(SimpleXMLElement $xml, $currentPage, $persistenceDrivers, CsrfTokenWrapper $csrf) {
		// set drivers
		$this->xml = $xml->security->authentication->oauth2;
		$this->setDrivers();
		
		// create dao object
		$locator = new DAOLocator($xml);
		$daoObject = $locator->locate($xml->security->authentication->oauth2, "dao", "Oauth2AuthenticationDAO");
		
		// setup class properties
		$this->authentication = new Oauth2Authentication($daoObject, $persistenceDrivers);

		// checks if a login action was requested, in which case it forwards
		$xmlLocal = $this->xml->driver;
		foreach($xmlLocal as $element) {
			$driverName = (string) $element["name"];
			$callbackPage = (string) $element["callback"];
			if($callbackPage == $currentPage) {
				$this->login($driverName, $element, $csrf);
			}
		}

		// checks if a logout action was requested, in which case it forwards
		$logoutPage = (string) $this->xml["logout"];
		if(!$logoutPage) $logoutPage = self::DEFAULT_LOGOUT_PAGE;
		if($logoutPage == $currentPage) {
			$this->logout();
		}
	}
	
	/**
	 * Logs user in (and registers if not found)
	 * 
	 * @param string $driverName Name of oauth2 driver (eg: facebook, google) that must exist as security.authentication.oauth2.{DRIVER} tag @ configuration.xml.
	 * @param SimpleXMLElement $element Object that holds XML info about driver
	 * @param CsrfTokenWrapper $csrf Object that performs CSRF token checks. 
	 * @throws ApplicationException If XML is malformed.
	 * @throws AuthenticationException If one or more persistence drivers are not instanceof PersistenceDriver
	 * @throws TokenException If CSRF checks fail.
	 * @throws SQLConnectionException If connection to database server fails.
	 * @throws SQLStatementException If query to database server fails.
	 * @throws OAuth2\ClientException When oauth2 local client sends malformed requests to oauth2 server.
	 * @throws OAuth2\ServerException When oauth2 remote server answers with an error.
	 */
	private function login($driverName, $element, CsrfTokenWrapper $csrf) {
		// detect class and load file
		$loginDriver = $this->getLoginDriver($driverName);

		// detect parameters from xml
		$authorizationCode = (!empty($_GET["code"])?$_GET["code"]:"");
		if($authorizationCode) {
			$targetSuccessPage = (string) $this->xml["target"];
			if(!$targetSuccessPage) $targetSuccessPage = self::DEFAULT_TARGET_PAGE;
			$targetFailurePage = (string) $this->xml["login"];
			if(!$targetFailurePage) $targetFailurePage = self::DEFAULT_LOGIN_PAGE;
			$createIfNotExists = (integer) $this->xml["auto_create"];
		
			// check state
			if($driverName != "VK") { // hardcoding: VK sends wrong state
				if(empty($_GET['state']) || !$csrf->isValid($_GET['state'], 0)) {
					throw new TokenException("CSRF token is invalid or missing!");
				}	
			}
			
			// get access token
			$accessTokenResponse = $this->drivers[$driverName]->getAccessToken($_GET["code"]);
			
			// get 
			$result = $this->authentication->login($loginDriver, $accessTokenResponse->getAccessToken(), $createIfNotExists);
			$this->setResult($result, $targetFailurePage, $targetSuccessPage);
			var_dump($this->result);
		} else {
			// get scopes
			$scopes = (string) $element["scopes"];
			if($scopes) $targetScopes = explode(",",$scopes);
			else $targetScopes = $loginDriver->getDefaultScopes();
		
			// set result
			$result = new AuthenticationResult(AuthenticationResultStatus::DEFERRED);
			$result->setCallbackURI($this->drivers[$driverName]->getAuthorizationCodeEndpoint($targetScopes, $csrf->generate(0)));
			$this->result = $result;
		}
	}
	
	/**
	 * Logs user out and empties all tokens for that user.
	 * 
	 * @throws SQLConnectionException If connection to database server fails.
	 * @throws SQLStatementException If query to database server fails.
	 */
	private function logout() {
		$loginPage = (string) $this->xml["login"];
		if(!$loginPage) $loginPage = self::DEFAULT_LOGIN_PAGE;
		
		$result = $this->authentication->logout();
		$this->setResult($result, $loginPage, $loginPage);
	}
	
	/**
	 * Builds an oauth2 client information object based on contents of security.authentication.oauth2.{DRIVER} tag @ configuration.xml.
	 * 
	 * @param SimpleXMLElement $xml Contents of security.authentication.oauth2.{DRIVER} tag @ configuration.xml.
	 * @throws ApplicationException If XML is malformed.
	 * @return \OAuth2\ClientInformation Encapsulates information about client that must match that in oauth2 remote server.
	 */
	private function getClientInformation(SimpleXMLElement $xml) {
		// get client id and secret from xml
		$clientID = (string) $xml["client_id"];
		$clientSecret = (string) $xml["client_secret"];
		if(!$clientID || !$clientSecret) throw new ApplicationException("Tags 'client_id' and 'client_secret' are mandatory!");
		
		// callback page is same as driver login page
		$callbackPage = (string) $xml["callback"];
		if(!$callbackPage) throw new ApplicationException("Tag 'callback' is mandatory!");
		
		$callbackPage = (isset($_SERVER['HTTPS'])?"https":"http")."://".$_SERVER['HTTP_HOST']."/".$callbackPage;
		return new OAuth2\ClientInformation($clientID, $clientSecret, $callbackPage);
	}
	
	/**
	 * Gets driver to interface OAuth2 operations with @ OAuth2Client API
	 * 
	 * @param string $driverName Name of OAuth2 vendor (eg: facebook)
	 * @param OAuth2\ClientInformation $clientInformation Object that encapsulates application credentials
	 * @throws ApplicationException If vendor is not found on disk.
	 * @return OAuth2\Driver Instance of driver that abstracts OAuth2 operations.
	 */
	private function getAPIDriver($driverName, OAuth2\ClientInformation $clientInformation) {
		$driverClass = $driverName."Driver";
		$driverFilePath = "vendor/lucinda/oauth2-client/drivers/".$driverClass.".php";
		if(!file_exists($driverFilePath)) throw new ApplicationException("Driver class not found: ".$driverFilePath);
		require_once($driverFilePath);
		return new $driverClass($clientInformation);
	}
	
	/**
	 * Gets driver that binds OAuthLogin @ Security API to OAuth2\Driver @ OAuth2Client API
	 * 
	 * @param string $driverName Name of OAuth2 vendor (eg: facebook)
	 * @throws ApplicationException If vendor is not found on disk.
	 * @return OAuthLogin Instance that performs OAuth2 login and collects user information.
	 */
	private function getLoginDriver($driverName) {
		$driverClass = $driverName."SecurityDriver";
		$driverFilePath = "application/models/oauth2/".$driverClass.".php";
		if(!file_exists($driverFilePath)) throw new ApplicationException("Driver class not found: ".$driverFilePath);
		require_once($driverFilePath);
		return new $driverClass($this->drivers[$driverName]);
	}
	
	/**
	 * Sets OAuth2\Driver instances based on XML
	 *
	 * @throws ApplicationException If required tags aren't found in XML / do not reflect on disk
	 */
	private function setDrivers() {
		$xmlLocal = $this->xml->driver;
		foreach($xmlLocal as $element) {
			$driverName = (string) $element["name"];
			if(!$driverName) throw new ApplicationException("Property 'name' of oauth2.driver tag is mandatory!");
		
			$clientInformation = $this->getClientInformation($element);
			$this->drivers[$driverName] = $this->getAPIDriver($driverName, $clientInformation);
			if($driverName == "GitHub") {
				$applicationName = (string) $element["application_name"];
				if(!$applicationName) throw new ApplicationException("Property 'application_name' of oauth2.driver tag is mandatory for GitHub!");
				$this->drivers[$driverName]->setApplicationName($applicationName);
			}
		}
	}
	
	/**
	 * Gets OAuth2 drivers
	 * 
	 * @return array[string:OAuth2\Driver] List of available oauth2 drivers by driver name.
	 */
	public function getDrivers() {
		return $this->drivers;
	}
}