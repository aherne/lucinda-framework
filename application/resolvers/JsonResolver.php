<?php
require_once("vendor/lucinda/framework-engine/src/Json.php");

/**
 * Implements a view resolver that renders JSON content.
 */
class JsonResolver extends Lucinda\MVC\STDOUT\ViewResolver {
    public function getContent() {
		$json = new Json();
		return $json->encode(array("status"=>"ok","body"=>$this->response->toArray()));
	}
}