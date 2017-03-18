<?php
class FileLogger extends DiskLogger {
	const EXTENSION = "log";
	private $filePath;
	private $rotationPattern;
	
	public function __construct($filePath, $rotationPattern="Y_m_d") {
		$this->filePath = $filePath;
		$this->rotationPattern = $rotationPattern;
	}
	
	protected function save($message, $logLevel) {
		error_log($message."\n", 3, $this->filePath.($this->rotationPattern?"__".date($this->rotationPattern):"").".".self::EXTENSION);
	}
}