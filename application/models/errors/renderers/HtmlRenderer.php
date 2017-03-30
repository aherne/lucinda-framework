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
	public function render(Exception $exception) {
		header_remove();
		header("Content-Type: text/html; charset=".$this->charset);
		if($exception instanceof PathNotFoundException) {
			header("HTTP/1.1 404 Not found");
			require_once("application/views/404.php");
		} else {
			header("HTTP/1.1 500 Internal server error");
			if($this->displayErrors) {
				require_once("application/views/debug.php");
			} else {
				require_once("application/views/500.php");
			}
		}
	}
}