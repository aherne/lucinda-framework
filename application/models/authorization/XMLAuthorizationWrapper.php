<?php
require_once("XMLAuthorization.php");
/**
 * <by_route logged_in_callback="" logged_out_callback=""/>
 */
class XMLAuthorizationWrapper {
	const DEFAULT_LOGGED_IN_PAGE = "index";
	const DEFAULT_LOGGED_OUT_PAGE = "login";
	const REFRESH_TIME = 0;
	
	public function __construct(SimpleXMLElement $xml, $currentPage, $userID) {
		$xmlLocal = $xml->security->authorization->by_route;
		
		$loggedInCallback = (string) $xmlLocal["logged_in_callback"];
		if(!$loggedInCallback) $loggedInCallback = self::DEFAULT_LOGGED_IN_PAGE;

		$loggedOutCallback = (string) $xmlLocal["logged_out_callback"];
		if(!$loggedOutCallback) $loggedOutCallback = self::DEFAULT_LOGGED_OUT_PAGE;
		
		$authorization = new XMLAuthorization($loggedInCallback, $loggedOutCallback);
		$result = $authorization->authorize($xml, $currentPage, ($userID?true:false));
		if($result->getStatus()==AuthorizationResult::STATUS_OK) {
			
		} else {
			header("HTTP/1.1 ".$this->getStatusText($result->getStatus()));
			header("Refresh:".self::REFRESH_TIME."; url=".$result->getCallbackURI()."?status=".$this->getStatusCode($result->getStatus()));
			exit();			
		}
	}
	
	private function getStatusCode($status) {
		if($status == AuthorizationResult::STATUS_UNAUTHORIZED) {
			return "UNAUTHORIZED";
		} else {
			return "NOT_FOUND";
		}
	}
	
	private function getStatusText($status) {
		if($status == AuthorizationResult::STATUS_UNAUTHORIZED) {
			return "401 Unauthorized";
		} else {
			return "404 Not Found";
		}
	}
}