<?php
/**
 * Default error renderer for HTML response format. You are invited to modify this class if you desire so.
 */
class HtmlRenderer implements ErrorRenderer {
	private $displayErrors;
	private $charset;
	
	/**
	 * Constructs an object
	 * 
	 * @param boolean $displayErrors Whether or not errors should be displayed on screen.
	 * @param string $charset Response charset.
	 */
	public function __construct($displayErrors=true, $charset="UTF-8") {
		$this->displayErrors = $displayErrors;
		$this->charset = $charset;
	}
	
	/**
	 * {@inheritDoc}
	 * @see ErrorRenderer::render()
	 */
	public function render($exception) {
		header("Content-Type: text/html; charset=".$this->charset);
		if($exception instanceof SecurityPacket) {
			$callback = $exception->getCallback();
			switch($exception->getStatus()) {
				case "unauthorized":
					header("HTTP/1.1 401 Unauthorized");
					require_once("application/views/401.php");
				case "forbidden":
					header("HTTP/1.1 403 Forbidden");
					require_once("application/views/403.php");
				case "not_found":
					header("HTTP/1.1 404 Not found");
					require_once("application/views/404.php");
				default:
					Response::sendRedirect($exception->getCallback().($exception->getStatus()!="redirect"?"?status=".$exception->getStatus():""),false,true);
					break;
			}
		} else if($exception instanceof HackingException) {
			header("HTTP/1.1 400 Bad Request");
			require_once("application/views/400.php");
		} else {
			header("HTTP/1.1 500 Internal server error");
			if($this->displayErrors) {
				require_once("application/views/debug.php");
			} else {
				require_once("application/views/500.php");
			}
		}
		exit();
	}
}
