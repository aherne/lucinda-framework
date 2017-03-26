<?php
require_once("SeverityFinder.php");

/**
 * Reports errors on disk using loggers.
 */
class DiskReporter implements ErrorReporter {
	private $logger;
	private $severityFinder;
	
	/**
	 * Uses logger to save errors to.
	 * 
	 * @param Logger $logger Logging provider instance
	 * @param SeverityFinder $severityFinder Checks severity of exception thrown
	 */
	public function __construct(Logger $logger, SeverityFinder $severityFinder) {
		$this->logger = $logger;
		$this->severityFinder = $severityFinder;
	}
	
	/**
	 * {@inheritDoc}
	 * @see ErrorReporter::report()
	 */
	public function report(Exception $exception) {
		$severity = $this->severityFinder->getSeverity($exception);
		
		switch($severity) {
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