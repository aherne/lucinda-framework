<?php
require_once("Logger.php");
/**
 * Abstracts logging into log files
 */
abstract class DiskLogger implements ErrorReporter, Logger {
	/**
	 * {@inheritDoc}
	 * @see Logger::log()
	 */
	public function log($message) {
		$trace = debug_backtrace()[0];
		$line = date("Y-m-d H:i:s")."\t"."DEBUG"."\t".$trace["file"]."\t".$trace["line"]."\t".$trace["message"];
		$this->save($line, LOG_DEBUG);
	}
	
	/**
	 * {@inheritDoc}
	 * @see ErrorReporter::report()
	 */
	public function report(Exception $exception) {
		$line = date("Y-m-d H:i:s")."\t".get_class($exception)."\t".$exception->getFile()."\t".$exception->getLine()."\t".$exception->getMessage();
		$this->save($line, LOG_ERR);
	}
	
	/**
	 * Saves error in log file.
	 * 
	 * @param string $message Error message.
	 * @param integer $logLevel Log level (eg: LOG_ERR, LOG_DEBUG)
	 */
	abstract protected function save($message, $logLevel);
}