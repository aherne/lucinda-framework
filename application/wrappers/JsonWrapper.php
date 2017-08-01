<?php
require_once("src/Json.php");

class JsonWrapper extends Wrapper {
	public function run() {
		$json = new Json();
		echo $json->encode(array("status"=>"ok","body"=>$this->objResponse->toArray()));
	}
}