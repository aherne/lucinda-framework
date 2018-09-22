<?php
require_once("vendor/lucinda/framework-engine/src/Json.php");

/**
 * View resolver for JSON response format
 */
class JsonResolver extends Lucinda\MVC\STDOUT\ViewResolver {
    /**
     * {@inheritDoc}
     * @see \Lucinda\MVC\STDOUT\ViewResolver::getContent()
     */
    public function getContent() {
		$json = new Lucinda\Framework\Json();
		return $json->encode(array("status"=>"ok","body"=>$this->response->attributes()->toArray()));
	}
}