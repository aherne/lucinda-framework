<?php
require_once("application/models/Json.php");

/**
 * Implements a view resolver that renders JSON content.
 */
class JsonWrapper extends Wrapper {
	public function run() {
		$json = new Json();
		echo $json->encode(array("status"=>"ok","body"=>$this->response->toArray()));
	}
}