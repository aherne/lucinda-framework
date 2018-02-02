<?php
/**
 * Error renderer for JSON response format.
 *
 * NOTICE: This class is designed to be open for change!
 */
class JsonRenderer implements ErrorRenderer {
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
		header("Content-Type: application/json; charset=".$this->charset);
        $payload = array();
        switch($className) {
            case "SecurityPacket":
                switch($exception->getStatus()) {
                    case "unauthorized":
                        header("HTTP/1.1 401 Unauthorized");
                        $payload = array("status"=>"unauthorized","body"=>"", "callback"=>$exception->getCallback());
                        break;
                    case "forbidden":
                        header("HTTP/1.1 403 Forbidden");
                        $payload = array("status"=>"forbidden","body"=>"", "callback"=>$exception->getCallback());
                        break;
                    case "not_found":
                        header("HTTP/1.1 404 Not found");
                        $payload = array("status"=>"not_found","body"=>"", "callback"=>$exception->getCallback());
                        break;
                    case "login_ok":
                        $payload = array("status"=>"login_ok","body"=>"", "callback"=>$exception->getCallback(), "token"=>$exception->getAccessToken());
                        break;
                    default:
                        $payload = array("status"=>$exception->getStatus(), "body"=>"", "callback"=>$exception->getCallback());
                        break;
                }
                break;
            case "MethodNotAllowedException":
                header("HTTP/1.1 405 Method Not Allowed");
                $payload = array("status"=>"method_not_allowed","body"=>"");
                break;
            case "SecurityException":
                header("HTTP/1.1 400 Bad Request");
                $payload = array("status"=>"bad_request","body"=>"");
                break;
            case "PathNotFoundException":
                header("HTTP/1.1 404 Not found");
                $payload = array("status"=>"not_found","body"=>"");
                break;
            default:
                header("HTTP/1.1 500 Internal server error");
                if($this->displayErrors) {
                    $payload = array("status"=>"error","body"=>$exception->getMessage());
                } else {
                    $payload = array("status"=>"error","body"=>"");
                }
                break;
        }
		echo json_encode($payload);
		die();
	}
}
