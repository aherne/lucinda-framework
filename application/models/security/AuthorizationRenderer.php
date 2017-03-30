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
	 * @throws PathNotFoundException If page requested does not exist.
	 */
	public function __construct(AuthorizationResult $result, $contentType, $contextPath) {
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
	 * @throws PathNotFoundException
	 */
	private function html(AuthorizationResult $result, $contextPath) {
		switch($result->getStatus()) {
			case AuthorizationResultStatus::UNAUTHORIZED:
				header("HTTP/1.1 401 Unauthorized");
				header("Location: ".$contextPath."/".$result->getCallbackURI()."?status=UNAUTHORIZED");
				exit();
				break;
			case AuthorizationResultStatus::FORBIDDEN:
				header("HTTP/1.1 403 Forbidden");
				header("Location: ".$contextPath."/".$result->getCallbackURI()."?status=FORBIDDEN");
				exit();
				break;
			case AuthorizationResultStatus::NOT_FOUND:
				throw new PathNotFoundException();
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
			case AuthorizationResultStatus::NOT_FOUND:
				$payload = array("status"=>"not_found","body"=>"");
				break;
		}

		// display payload
		header("Content-Type: application/json");
		echo json_encode($payload);
		exit();
	}
}