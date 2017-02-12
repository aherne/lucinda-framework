<?php
require_once("libraries/php-security-api/src/authorization/DAOAuthorization.php");

class DAOAuthorizationWrapper {
	const DEFAULT_LOGGED_IN_PAGE = "index";
	const DEFAULT_LOGGED_OUT_PAGE = "login";
	const REFRESH_TIME = 0;
	
	public function __construct(SimpleXMLElement $xml, $currentPage, $userID, DAOLocator $locator) {
		$loggedInCallback = (string) $xml->logged_in_callback;
		if(!$loggedInCallback) $loggedInCallback = self::DEFAULT_LOGGED_IN_PAGE;
		
		$loggedOutCallback = (string) $xml->logged_out_callback;
		if(!$loggedOutCallback) $loggedOutCallback = self::DEFAULT_LOGGED_OUT_PAGE;
		
		$pageDAO = $locator->locate($xml, "page_dao", "PageAuthorizationDAO");
		$pageDAO->setPage($currentPage);
		
		$userDAO = $locator->locate($xml, "user_dao", "UserAuthorizationDAO");
		$userDAO->setUserID($userID);
		
		$authorization = new DAOAuthorization($loggedInCallback, $loggedOutCallback);
		$result = $authorization->authorize($pageDAO, $userDAO);
		if($result->getStatus()!=AuthorizationResult::STATUS_OK) {
			header("HTTP/1.1 ".$this->getStatusText($result->getStatus()));
			header("Refresh:".self::REFRESH_TIME."; url=".$result->getCallbackURI()."?status=".$this->getStatusCode($result->getStatus()));
			exit();
		}
	}
}