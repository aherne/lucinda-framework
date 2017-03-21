<?php
require_once("DiskLogger.php");
/**
 * Logs messages/errors into simple files.
 */
class FileLogger extends DiskLogger {
	const EXTENSION = "log";
	private $filePath;
	private $rotationPattern;
	
	/**
	 * Creates logger instance.
	 * 
	 * @param string $filePath Log file (without extension) and its absolute path.
	 * @param string $rotationPattern PHP date function format by which logs will rotate.
	 */
	public function __construct($filePath, $rotationPattern="Y_m_d") {
		$this->filePath = $filePath;
		$this->rotationPattern = $rotationPattern;
	}
	
	/**
	 * {@inheritDoc}
	 * @see DiskLogger::save()
	 */
	protected function save($message, $logLevel) {
		error_log($message."\n", 3, $this->filePath.($this->rotationPattern?"__".date($this->rotationPattern):"").".".self::EXTENSION);
	}
}