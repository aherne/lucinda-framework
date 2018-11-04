<?php
require_once("application/models/json/Json.php");

/**
 * View resolver for JSON response format
 */
class JsonResolver extends Lucinda\MVC\STDOUT\ViewResolver {
    /**
     * {@inheritDoc}
     * @see \Lucinda\MVC\STDOUT\ViewResolver::getContent()
     */
    public function getContent() {
		$json = new Json();
		return $json->encode(array("status"=>"ok","body"=>$this->response->attributes()->toArray()));
	}
}