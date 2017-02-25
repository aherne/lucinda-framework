<?php
/**
 * Binds DAOAuthorization @ SECURITY-API to settings from configuration.xml @ SERVLETS-API then performs request authorization.
 */
class DAOAuthorizationWrapper {
	const DEFAULT_LOGGED_IN_PAGE = "index";
	const DEFAULT_LOGGED_OUT_PAGE = "login";
	const REFRESH_TIME = 0;
	
	private $result;

	public function __construct(SimpleXMLElement $xml, $currentPage, $userID, DAOLocator $locator) {
		$this->setResult($xml, $currentPage, $userID, $locator);
// 		if($result->getStatus()!=AuthorizationResultStatus::OK) {
// 			header("HTTP/1.1 ".$this->getStatusText($result->getStatus()));
// 			header("Refresh:".self::REFRESH_TIME."; url=".$result->getCallbackURI()."?status=".$this->getStatusCode($result->getStatus()));
// 			exit();
// 		}
	}
	
	/**
	 * Extracts relevant info from XML and delegates it to DAOAuthorization, saving authorization result.
	 * 
	 * @param SimpleXMLElement $xml XML tag that sets up by_dao authorization.
	 * @param string $currentPage Requested page path.
	 * @param mixed $userID Unique user identifier (usually an integer)
	 * @param DAOLocator $locator Service that locates data-access-objects in XML and on disk, then validates and instantiates them.
	 */
	private function setResult(SimpleXMLElement $xml, $currentPage, $userID, DAOLocator $locator) {
		$loggedInCallback = (string) $xml["logged_in_callback"];
		if(!$loggedInCallback) $loggedInCallback = self::DEFAULT_LOGGED_IN_PAGE;
		
		$loggedOutCallback = (string) $xml["logged_out_callback"];
		if(!$loggedOutCallback) $loggedOutCallback = self::DEFAULT_LOGGED_OUT_PAGE;
		
		$pageDAO = $locator->locate($xml, "page_dao", "PageAuthorizationDAO");
		$pageDAO->setID($currentPage);
		
		$userDAO = $locator->locate($xml, "user_dao", "UserAuthorizationDAO");
		$userDAO->setID($userID);
		
		$authorization = new DAOAuthorization($loggedInCallback, $loggedOutCallback);
		$this->result = $authorization->authorize($pageDAO, $userDAO);
	}
	
	/**
	 * Gets result of authorization attempt
	 * 
	 * @return AuthorizationResult
	 */
	public function getResult() {
		return $this->result;
	}

// 	private function getStatusText($statusID) {
// 		switch($statusID) {
// 			case AuthorizationResultStatus::UNAUTHORIZED:
// 				return "401 Unauthorized";
// 				break;
// 			case AuthorizationResultStatus::FORBIDDEN:
// 				return "403 Forbidden";
// 				break;
// 			case AuthorizationResultStatus::NOT_FOUND:
// 				return "404 Not Found";
// 				break;
// 		}
// 	}

// 	private function getStatusCode($statusID) {
// 		switch($statusID) {
// 			case AuthorizationResultStatus::UNAUTHORIZED:
// 				return "MUST_LOGIN";
// 				break;
// 			case AuthorizationResultStatus::FORBIDDEN:
// 				return "NOT_ALLOWED";
// 				break;
// 			case AuthorizationResultStatus::NOT_FOUND:
// 				return "NOT_FOUND";
// 				break;
// 		}
// 	}
}