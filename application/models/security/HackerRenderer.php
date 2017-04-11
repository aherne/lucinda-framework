<?php
class HackerRenderer {
	public function __construct(Exception $exception, $contentType, $contextPath) {
		header("HTTP/1.1 400 Bad Request");
		if($contentType == "text/html") {
			$this->html($exception, $contextPath);
		} else if ($contentType == "application/json") {
			$this->json($exception);
		} else {
			throw new ApplicationException("Renderer not defined for: ".$contentType);
		}
	}
	
	private function html(Exception $exception, $contextPath) {
		require_once("application/views/400.php");
		exit();
	}
	
	private function json(Exception $exception) {
		require_once("application/views/400.php");
		exit();
	}
}