<?php
require_once("ErrorSeverityFinder.php");
/**
 * Reports errors on disk using loggers.
 */
class LogReporter implements ErrorReporter {
	private $logger;
	private $inspector;
	
	/**
	 * Uses logger to save errors to.
	 * 
	 * @param Logger $logger Logging provider instance
	 * @param ErrorSeverityFinder $inspector Class that inspects errors for their logging severity.
	 */
	public function __construct(Logger $logger, ErrorSeverityFinder $inspector) {
		$this->logger = $logger;
		$this->inspector= $inspector;
	}
	
	/**
	 * {@inheritDoc}
	 * @see ErrorReporter::report()
	 */
	public function report($exception) {
		$severity = $this->inspector->getSeverity($exception);
		switch($severity) {
			case LOG_NONE: 	// on errors that need not be reported
				break;
			case LOG_EMERG: // on server failures
				$this->logger->emergency($exception);
				break;
			case LOG_ALERT: // on programming failures that cause a halt in session
				$this->logger->alert($exception);
				break;
			case LOG_CRIT:	// on client failures
				$this->logger->critical($exception);
				break;
			default:		// on checked failures
				$this->logger->error($exception);
				break;			
		}
	}
}