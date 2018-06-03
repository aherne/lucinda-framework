<?php
/**
 * Implements a logger that forwards internally to multiple loggers.
 */
class MultiLogger extends Logger {
	private $loggers;
	
	/**
	 * Creates an object.
	 * 
	 * @param Logger[] $loggers List of loggers to delegate logging to.
	 */
	public function __construct($loggers) {
		$this->loggers = $loggers;
	}
	
	/**
	 * {@inheritDoc}
	 * @see Logger::log()
	 */
	public function log($info, $level) {
		foreach($this->loggers as $logger) {
			$logger->log($info, $level);
		}
	}
}