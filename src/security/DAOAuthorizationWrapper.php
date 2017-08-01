<?php
require_once("AuthorizationWrapper.php");
/**
 * Binds DAOAuthorization @ SECURITY-API to settings from configuration.xml @ SERVLETS-API then performs request authorization via database.
 */
class DAOAuthorizationWrapper extends AuthorizationWrapper {
	const DEFAULT_LOGGED_IN_PAGE = "index";
	const DEFAULT_LOGGED_OUT_PAGE = "login";
	const REFRESH_TIME = 0;
	
	/**
	 * Creates an object
	 * 
	 * @param SimpleXMLElement $xml Contents of security.authorization.by_dao tag @ configuration.xml
	 * @param string $currentPage Current page requested.
	 * @param mixed $userID Unique user identifier (usually an integer) 
	 * @throws SQLConnectionException If connection to database server fails.
	 * @throws SQLStatementException If query to database server fails.
	 */
	public function __construct(SimpleXMLElement $xml, $currentPage, $userID) {
		// create dao object
		$locator = new DAOLocator($xml);
		$xml = $xml->security->authorization->by_dao;
		
		$loggedInCallback = (string) $xml["logged_in_callback"];
		if(!$loggedInCallback) $loggedInCallback = self::DEFAULT_LOGGED_IN_PAGE;
		
		$loggedOutCallback = (string) $xml["logged_out_callback"];
		if(!$loggedOutCallback) $loggedOutCallback = self::DEFAULT_LOGGED_OUT_PAGE;
		
		$pageDAO = $locator->locate($xml, "page_dao", "PageAuthorizationDAO");
		$pageDAO->setID($currentPage);
		
		$userDAO = $locator->locate($xml, "user_dao", "UserAuthorizationDAO");
		$userDAO->setID($userID);
		
		$authorization = new DAOAuthorization($loggedInCallback, $loggedOutCallback);
		$this->setResult($authorization->authorize($pageDAO, $userDAO, $_SERVER["REQUEST_METHOD"]));
	}
}