<?php
require_once("AuthenticationWrapper.php");

/**
 * Binds OAuth2Authentication @ SECURITY-API and Driver @ OAUTH2-CLIENT-API with settings from configuration.xml @ SERVLETS-API and vendor-specific 
 * (eg: google / facebook) driver implementation, then performs login/logout if path requested matches paths @ xml.
 */
class Oauth2AuthenticationWrapper extends AuthenticationWrapper {
	const DEFAULT_CALLBACK_PAGE = "{DRIVER}/login";
	const DEFAULT_LOGIN_PAGE = "login";
	const DEFAULT_LOGOUT_PAGE = "logout";
	const DEFAULT_TARGET_PAGE = "index";
	
	private $xml;
	private $authentication;
	
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
		// create dao object
		$locator = new DAOLocator($xml);
		$daoObject = $locator->locate($xml->security->authentication->oauth2, "dao", "Oauth2AuthenticationDAO");
		
		// setup class properties
		$this->xml = $xml->security->authentication->oauth2;
		$this->authentication = new Oauth2Authentication($daoObject, $persistenceDrivers);

		// checks if a login action was requested, in which case it forwards
		$xmlLocal = $this->xml->driver;
		foreach($xmlLocal as $element) {
			$driverName = (string) $element["name"];
			if(!$driverName) throw new ApplicationException("Property 'name' of oauth2.driver tag is mandatory!");
		
			$callbackPage = (string) $element["callback"];
			if(!$callbackPage) $callbackPage = str_replace("{DRIVER}", $driverName, self::DEFAULT_CALLBACK_PAGE);
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
	 * @param SimpleXMLElement $element Contents of security.authentication.oauth2.{DRIVER} tag @ configuration.xml.
	 * @param CsrfTokenWrapper $csrf Object that performs CSRF token checks. 
	 * @throws ApplicationException If XML is malformed.
	 * @throws AuthenticationException If one or more persistence drivers are not instanceof PersistenceDriver
	 * @throws TokenException If CSRF checks fail.
	 * @throws SQLConnectionException If connection to database server fails.
	 * @throws SQLStatementException If query to database server fails.
	 * @throws OAuth2\ClientException When oauth2 local client sends malformed requests to oauth2 server.
	 * @throws OAuth2\ServerException When oauth2 remote server answers with an error.
	 */
	private function login($driverName, SimpleXMLElement $element, CsrfTokenWrapper $csrf) {
		// detect class and load file
		$driverClass = ucwords($driverName)."Driver";
		$driverFilePath = "oauth2login/".$driverClass.".php";
		if(!file_exists($driverFilePath)) throw new ApplicationException("Driver class not found: ".$driverFilePath);
		require_once($driverFilePath);
		
		$clientInformation = $this->getClientInformation($element);
		$driver = new $driverClass($clientInformation);

		// detect parameters from xml
		$authorizationCode = (!empty($_GET["code"])?$_GET["code"]:"");
		if($authorizationCode) {
			$targetSuccessPage = (string) $this->xml["target"];
			if(!$targetSuccessPage) $targetSuccessPage = self::DEFAULT_TARGET_PAGE;
			$targetFailurePage = (string) $this->xml["login"];
			if(!$targetFailurePage) $targetFailurePage = self::DEFAULT_LOGIN_PAGE;
			$createIfNotExists = (integer) $this->xml["auto_create"];
		
			// check state
			if(empty($_GET['state']) || !$csrf->isValid($_GET['state'], 0)) {
				throw new TokenException("CSRF token is invalid or missing!");
			}
			
			// get access token
			$accessTokenResponse = $driver->getAccessToken($_GET["code"]);
			
			// get 
			$result = $this->authentication->login($driver, $accessTokenResponse->getAccessToken(), $createIfNotExists);
			$this->setResult($result, $targetFailurePage, $targetSuccessPage);
		} else {
			// get scopes
			$scopes = (string) $element["scopes"];
			if($scopes) $targetScopes = explode(",",$scopes);
			else $targetScopes = $driver->getDefaultScopes();
		
			// set result
			$result = new AuthenticationResult(AuthenticationResultStatus::DEFERRED);
			$result->setCallbackURI($driver->getAuthorizationCodeEndpoint($targetScopes, $csrf->generate(0)));
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
		$callbackPage = (isset($_SERVER['HTTPS'])?"https":"http")."://".$_SERVER['HTTP_HOST'].str_replace("?".$_SERVER["QUERY_STRING"],"",$_SERVER['REQUEST_URI']);
		return new OAuth2\ClientInformation($clientID, $clientSecret, $callbackPage);
	}
}