<?php
/**
 * Renders view on authentication attempts depending on requested content type.
 */
class AuthenticationRenderer {
	/**
	 * Performs rendering
	 * 
	 * @param AuthenticationResult $result Results of authentication attempt.
	 * @param string $contentType Content type of page requested (default or client-specified)
	 * @param string $contextPath Context path relative to page requested (eg: /my_application for http://localhost/my_application/my_page)
	 * @param PersistenceDriver[] $persistenceDrivers List of persistence drivers
	 * @throws ApplicationException If no renderer is defined for requested content type
	 */
	public function __construct(AuthenticationResult $result, $contentType, $contextPath, $persistenceDrivers) {
		if($contentType == "text/html") {
			$this->html($result, $contextPath);
		} else if ($contentType == "application/json") {
			$this->json($result, $persistenceDrivers);
		} else {
			throw new ApplicationException("Renderer not defined for: ".$contentType);
		}
	}
	
	/**
	 * Renders a HTML response.
	 *
	 * @param AuthenticationResult $result
	 * @param string $contextPath
	 */
	private function html(AuthenticationResult $result, $contextPath) {
		// construct redirection path
		$redirectionPath = "";
		switch($result->getStatus()) {
			case AuthenticationResultStatus::OK:
				$redirectionPath = $contextPath."/".$result->getCallbackURI()."?status=OK";
				break;
			case AuthenticationResultStatus::DEFERRED:
				$redirectionPath = $result->getCallbackURI();
				break;
			case AuthenticationResultStatus::LOGIN_FAILED:
				$redirectionPath = $contextPath."/".$result->getCallbackURI()."?status=login_failed";
				break;
			case AuthenticationResultStatus::LOGOUT_FAILED:
				$redirectionPath = $$contextPath."/".$result->getCallbackURI()."?status=logout_failed";
				break;
		}
		
		// redirect
		header("Location: ".$redirectionPath);
		exit();
	}
	
	/**
	 * Renders a JSON response.
	 *
	 * @param AuthenticationResult $result
	 * @param PersistenceDriver[] $persistenceDrivers
	 */
	private function json(AuthenticationResult $result, $persistenceDrivers) {
		// construct json payload
		$payload = array();
		switch($result->getStatus()) {
			case AuthenticationResultStatus::OK:
				// retrieve token from persistence driver
				$token = "";
				if($result->getUserID()) {
					foreach($persistenceDrivers as $persistenceDriver) {
						if($persistenceDriver instanceof TokenPersistenceDriverWrapper) {
							$token = $persistenceDriver->getDriver()->getAccessToken();
						}
					}
				}
				// display result
				$payload = array("status"=>"ok","body"=>$token);
				break;
			case AuthenticationResultStatus::DEFERRED:
				$payload = array("status"=>"redirect","body"=>$result->getCallbackURI());
				break;
			case AuthenticationResultStatus::LOGIN_FAILED:
				$payload = array("status"=>"login_failed","body"=>"");
				break;
			case AuthenticationResultStatus::LOGOUT_FAILED:
				$payload = array("status"=>"logout_failed","body"=>"");
				break;
		}
		
		// display payload
		header("Content-Type: application/json");
		echo json_encode($payload);
		exit();
	}
}