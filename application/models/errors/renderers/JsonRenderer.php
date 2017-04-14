<?php
/**
 * Default error renderer for JSON response format. You are invited to modify this class if you desire so.
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
		header_remove();
		header("Content-Type: application/json; charset=".$this->charset);
		if($exception instanceof SecurityPacket) {
			switch($exception->getStatus()) {
				case "unauthorized":
					header("HTTP/1.1 401 Unauthorized");
					echo json_encode(array("status"=>"unauthorized","body"=>"", "callback"=>$exception->getCallback()));
				case "forbidden":
					header("HTTP/1.1 403 Forbidden");
					echo json_encode(array("status"=>"forbidden","body"=>"", "callback"=>$exception->getCallback()));
				case "not_found":
					header("HTTP/1.1 404 Not found");
					echo json_encode(array("status"=>"not_found","body"=>"", "callback"=>$exception->getCallback()));
				case "login_ok":
					echo json_encode(array("status"=>"login_ok","body"=>"", "callback"=>$exception->getCallback(), "token"=>$exception->getAccessToken()));
				default:
					echo json_encode(array("status"=>($exception->getStatus()?$exception->getStatus():"redirect"),"body"=>"", "callback"=>$exception->getCallback()));
					break;
			}
		} else if($exception instanceof PathNotFoundException) {
			header("HTTP/1.1 404 Not found");
			echo json_encode(array("status"=>"not_found","body"=>""));
		} else {
			header("HTTP/1.1 500 Internal server error");
			if($this->displayErrors) {
				echo json_encode(array("status"=>"error","body"=>$exception->getMessage()));
			} else {
				echo json_encode(array("status"=>"error","body"=>""));
			}
		}
	}
}