<?php
/**
 * Reports errors on disk using loggers.
 */
class LogReporter implements ErrorReporter {
	private $logger;
	private $severityFinder;
	
	/**
	 * Uses logger to save errors to.
	 * 
	 * @param Logger $logger Logging provider instance
	 * @param SeverityFinder $severityFinder Checks severity of exception thrown
	 */
	public function __construct(Logger $logger) {
		$this->logger = $logger;
		$this->severityFinder = new ErrorSeverityFinder();
	}
	
	/**
	 * {@inheritDoc}
	 * @see ErrorReporter::report()
	 */
	public function report($exception) {
		$severity = $this->getSeverity($exception);
		
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
	
	/**
	 * Gets error syslog severity.
	 * 
	 * @param Error|Exception $exception
	 * @return string
	 */
	private  function getSeverity($exception) {
		if($exception instanceof Error) {
			return LOG_CRIT; 	// programmer fault
		} else if($exception instanceof PHPException) {
			return LOG_CRIT; 	// programmer fault
		} else if($exception instanceof NoSQLConnectionException) {
			return LOG_EMERG; 	// server fault
		} else if($exception instanceof NoSQLStatementException) {
			return LOG_CRIT; 	// programmer fault
		} else if($exception instanceof SQLConnectionException) {
			return LOG_EMERG;	// server fault
		} else if($exception instanceof SQLStatementException) {
			return LOG_CRIT; 	// programmer fault
		} else if($exception instanceof ServletException) {
			return LOG_ALERT;	// programmer fault
		} else if($exception instanceof ApplicationException) {
			return LOG_ALERT; 	// programmer fault
		} else if($exception instanceof AuthenticationException) {
			return LOG_ALERT; 	// programmer fault
		} else if($exception instanceof ViewException) {
			return LOG_CRIT;	// programmer fault
		} else {
			return LOG_ERR;		// client fault (in principle)
		}
	}
}