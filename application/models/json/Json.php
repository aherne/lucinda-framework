<?php
if(!class_exists("JsonException")) require_once("JsonException.php");

/**
 * Simple wrapper over json functionality.
 */
class Json {
	/**
	 * Encodes data into JSON format.
	 * 
	 * @param mixed $data
	 * @return string
	 * @throws JsonException If encoding of mixed data into json failed
	 */
	public function encode($data) {
		$result = json_encode($data, JSON_UNESCAPED_UNICODE);
		$this->checkError();
		return $result;
	}
	
	/**
	 * Decodes JSON into original php data type.
	 * 
	 * @param string $json
	 * @param boolean $assoc
	 * @return mixed
	 * @throws JsonException If decoding of json into array failed
	 */
	public function decode($json, $assoc=true) {
		$result = json_decode($json, $assoc);
		$this->checkError();
		return $result;
	}
	
	/**
	 * Checks if encoding/decoding went without error. If error, throws JsonException.
	 * 
	 * @throws JsonException If decoding/encoding of json failed.
	 */
	private function checkError() {
		$errorID = json_last_error();
		
		// everything went well
		if($errorID == JSON_ERROR_NONE) return;
		
		throw new JsonException(json_last_error_msg());
	}
}
