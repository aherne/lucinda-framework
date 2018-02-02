<?php
/**
 * Error renderer for HTML response format.
 *
 * NOTICE: This class is designed to be open for change!
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
	    $className = get_class($exception);
		header("Content-Type: text/html; charset=".$this->charset);
		switch($className) {
            case "SecurityPacket":
                switch($exception->getStatus()) {
                    case "unauthorized":
                        header("HTTP/1.1 401 Unauthorized");
                        require_once("application/views/401.html");
                        break;
                    case "forbidden":
                        header("HTTP/1.1 403 Forbidden");
                        require_once("application/views/403.html");
                        break;
                    case "not_found":
                        header("HTTP/1.1 404 Not found");
                        require_once("application/views/404.html");
                        break;
                    default:
                        Response::sendRedirect($exception->getCallback().($exception->getStatus()!="redirect"?"?status=".$exception->getStatus():""),false,true);
                        break;
                }
                break;
            case "MethodNotAllowedException":
                header("HTTP/1.1 405 Method Not Allowed");
                require_once("application/views/405.html");
                break;
            case "SecurityException":
                header("HTTP/1.1 400 Bad Request");
                require_once("application/views/400.html");
                break;
            case "PathNotFoundException":
                header("HTTP/1.0 404 Not Found");
                require_once("application/views/404.html");
                break;
            default:
                header("HTTP/1.1 500 Internal server error");
                if($this->displayErrors) {
                    require_once("application/views/debug.php");
                } else {
                    require_once("application/views/500.html");
                }
                break;
        }
		die();
	}
}
