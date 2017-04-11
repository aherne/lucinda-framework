<?php
/**
 * Renders view on authorization failures depending on requested content type.
 */
class AuthorizationRenderer {
	/**
	 * Performs rendering.
	 *
	 * @param AuthorizationResult $result Results of authorization attempt.
	 * @param string $contentType Content type of page requested (default or client-specified)
	 * @param string $contextPath Context path relative to page requested (eg: /my_application for http://localhost/my_application/my_page)
	 * @throws ApplicationException If no renderer is defined for requested content type
	 * @throws PathNotFoundException
	 */
	public function __construct(AuthorizationResult $result, $contentType, $contextPath) {
		if($result->getStatus() == AuthorizationResultStatus::NOT_FOUND) {
			throw new PathNotFoundException();
		}
		if($contentType == "text/html") {
			$this->html($result, $contextPath);
		} else if ($contentType == "application/json") {
			$this->json($result);
		} else {
			throw new ApplicationException("Renderer not defined for: ".$contentType);
		}
	}
	
	/**
	 * Renders a HTML response.
	 *
	 * @param AuthorizationResult $result
	 * @param string $contextPath
	 */
	private function html(AuthorizationResult $result, $contextPath) {
		$callback = $contextPath."/".$result->getCallbackURI();
		switch($result->getStatus()) {
			case AuthorizationResultStatus::UNAUTHORIZED:
				header("HTTP/1.1 401 Unauthorized");
				require_once("application/views/401.php");
				exit();
				break;
			case AuthorizationResultStatus::FORBIDDEN:
				header("HTTP/1.1 403 Forbidden");
				require_once("application/views/403.php");
				exit();
				break;
		}
	}
	
	/**
	 * Renders a JSON response.
	 *
	 * @param AuthorizationResult $result
	 */
	private function json(AuthorizationResult $result) {
		// construct json payload
		$payload = array();
		switch($result->getStatus()) {
			case AuthorizationResultStatus::UNAUTHORIZED:
				$payload = array("status"=>"unauthorized","body"=>"");
				break;
			case AuthorizationResultStatus::FORBIDDEN:
				$payload = array("status"=>"forbidden","body"=>"");
				break;
		}
		
		// display payload
		header("Content-Type: application/json");
		echo json_encode($payload);
		exit();
	}
}