<?php
/**
 * Reports errors on disk using loggers.
 */
class DiskReporter implements ErrorReporter {
	private $logger;
	
	/**
	 * Uses logger to save errors to.
	 * 
	 * @param Logger $logger Logging provider instance
	 */
	public function __construct(Logger $logger) {
		$this->logger = $logger;
	}
	
	/**
	 * {@inheritDoc}
	 * @see ErrorReporter::report()
	 */
	public function report(Exception $exception) {
		if($exception instanceof PHPException) {
			$this->logger->critical($exception);
		} else if($exception instanceof NoSQLConnectionException) {
			$this->logger->emergency($exception); 	// server fault
		} else if($exception instanceof NoSQLStatementException) {
			$this->logger->critical($exception); 	// programmer fault
		} else if($exception instanceof SQLConnectionException) {
			$this->logger->emergency($exception);	// server fault
		} else if($exception instanceof SQLStatementException) {
			$this->logger->critical($exception); 	// programmer fault
		} else if($exception instanceof AuthenticationException) {
			$this->logger->critical($exception); 	// programmer fault
		} else if($exception instanceof SessionHijackException) {
			$this->logger->error($exception); 		// client fault (hacking attempt)
		} else if($exception instanceof EncryptionException) {
			$this->logger->error($exception); 		// client fault (hacking attempt)
		} else if($exception instanceof TokenException) {
			$this->logger->error($exception); 		// client fault (hacking attempt)
		} else if($exception instanceof ApplicationException) {
			$this->logger->critical($exception); 	// programmer fault
		} else if($exception instanceof FileUploadException) {
			$this->logger->error($exception); 		// client fault
		} else if($exception instanceof FormatNotFoundException) {
			$this->logger->critical($exception); 	// client fault
		} else if($exception instanceof PathNotFoundException) {
			$this->logger->critical($exception); 	// client fault
		} else if($exception instanceof ServletException) {
			$this->logger->critical($exception);	// programmer fault
		} else if($exception instanceof ViewException) {
			$this->logger->error($exception);		// programmer fault
		} else {
			$this->logger->error($exception);
		}
	}
}