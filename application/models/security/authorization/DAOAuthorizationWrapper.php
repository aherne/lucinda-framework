<?php
require_once("libraries/php-security-api/src/authorization/DAOAuthorization.php");

class DAOAuthorizationWrapper {
	const DEFAULT_LOGGED_IN_PAGE = "index";
	const DEFAULT_LOGGED_OUT_PAGE = "login";
	const REFRESH_TIME = 0;

	public function __construct(SimpleXMLElement $xml, $currentPage, $userID, DAOLocator $locator) {
		$loggedInCallback = (string) $xml["logged_in_callback"];
		if(!$loggedInCallback) $loggedInCallback = self::DEFAULT_LOGGED_IN_PAGE;

		$loggedOutCallback = (string) $xml["logged_out_callback"];
		if(!$loggedOutCallback) $loggedOutCallback = self::DEFAULT_LOGGED_OUT_PAGE;

		$pageDAO = $locator->locate($xml, "page_dao", "PageAuthorizationDAO");
		$pageDAO->setID($currentPage);

		$userDAO = $locator->locate($xml, "user_dao", "UserAuthorizationDAO");
		$userDAO->setID($userID);

		$authorization = new DAOAuthorization($loggedInCallback, $loggedOutCallback);
		$result = $authorization->authorize($pageDAO, $userDAO);
		if($result->getStatus()!=AuthorizationResult::STATUS_OK) {
			header("HTTP/1.1 ".$this->getStatusText($result->getStatus()));
			header("Refresh:".self::REFRESH_TIME."; url=".$result->getCallbackURI()."?status=".$this->getStatusCode($result->getStatus()));
			exit();
		}
	}

	private function getStatusText($statusID) {
		switch($statusID) {
			case AuthorizationResult::STATUS_UNAUTHORIZED:
				return "401 Unauthorized";
				break;
			case AuthorizationResult::STATUS_FORBIDDEN:
				return "403 Forbidden";
				break;
			case AuthorizationResult::STATUS_NOT_FOUND:
				return "404 Not Found";
				break;
		}
	}

	private function getStatusCode($statusID) {
		switch($statusID) {
			case AuthorizationResult::STATUS_UNAUTHORIZED:
				return "MUST_LOGIN";
				break;
			case AuthorizationResult::STATUS_FORBIDDEN:
				return "NOT_ALLOWED";
				break;
			case AuthorizationResult::STATUS_NOT_FOUND:
				return "NOT_FOUND";
				break;
		}
	}
}