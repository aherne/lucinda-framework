<?php
class FileLogger implements ErrorReporter {
	const EXTENSION = "log";
	private $filePath;
	private $rotationPattern;
	
	public function __construct($filePath, $rotationPattern="Y_m_d") {
		$this->filePath = $filePath;
		$this->rotationPattern = $rotationPattern;
	}
	
	public function debug($message) {
		$trace = debug_backtrace()[0];
		$line = date("Y-m-d H:i:s")."\t"."DEBUG"."\t".$trace["file"]."\t".$trace["line"]."\t".$trace["message"];
		$this->log($line);
	}
	
	public function report(Exception $exception) {
		$line = date("Y-m-d H:i:s")."\t".get_class($exception)."\t".$exception->getFile()."\t".$exception->getLine()."\t".$exception->getMessage();
		$this->log($line);
	}
	
	private function log($message) {
		error_log($message."\n", 3, $this->filePath.($this->rotationPattern?"__".date($this->rotationPattern):"").".".self::EXTENSION);
	}
}