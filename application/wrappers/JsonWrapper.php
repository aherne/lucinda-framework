<?php
require_once("src/Json.php");

/**
 * Implements a view resolver that renders JSON content.
 */
class JsonWrapper extends Wrapper {
	public function run() {
		$json = new Json();
		echo $json->encode(array("status"=>"ok","body"=>$this->response->toArray()));
	}
}