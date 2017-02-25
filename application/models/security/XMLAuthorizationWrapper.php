<?php
/**
 * Binds XMLAuthorization @ SECURITY-API to settings from configuration.xml @ SERVLETS-API then performs request authorization.
 */
class XMLAuthorizationWrapper {
	const DEFAULT_LOGGED_IN_PAGE = "index";
	const DEFAULT_LOGGED_OUT_PAGE = "login";
	const REFRESH_TIME = 0;
	
	private $result;
	
	public function __construct(SimpleXMLElement $xml, $currentPage, $userID) {
		$this->setResult($xml, $currentPage, $userID);
	}

	/**
	 * Extracts relevant info from XML and delegates it to XMLAuthorization, saving authorization result.
	 * 
	 * @param SimpleXMLElement $xml XML root tag containing security.authorization.by_route subtag.
	 * @param string $currentPage Requested page path.
	 * @param mixed $userID Unique user identifier (usually an integer)
	 * @throws ApplicationException If XML structure is invalid.
	 */
	private function setResult(SimpleXMLElement $xml, $currentPage, $userID) {
		// check autorouting
		$autoRouting = (int) $xml->application->auto_routing;
		if($autoRouting) {
			throw new ApplicationException("XML authorization does not support auto-routing!");
		}
		
		// move up in xml tree
		$xmlLocal = $xml->security->authorization->by_route;
		
		$loggedInCallback = (string) $xmlLocal["logged_in_callback"];
		if(!$loggedInCallback) $loggedInCallback = self::DEFAULT_LOGGED_IN_PAGE;
		
		$loggedOutCallback = (string) $xmlLocal["logged_out_callback"];
		if(!$loggedOutCallback) $loggedOutCallback = self::DEFAULT_LOGGED_OUT_PAGE;
		
		// authorize and save result
		try {
			$authorization = new XMLAuthorization($loggedInCallback, $loggedOutCallback);
			$this->result = $authorization->authorize($xml, $currentPage, ($userID?true:false));
		} catch(XMLException $e) {
			throw new ApplicationException($e->getMessage());
		}
	}

	/**
	 * Gets result of authorization attempt
	 *
	 * @return AuthorizationResult
	 */
	public function getResult() {
		return $this->result;
	}
}