<?php
define("LOG_NONE", -1);

/**
 *  Inspects errors for severity & other criteria (TBD)
 */
class ErrorInspector implements ErrorSeverityFinder {
	/**
	 * {@inheritDoc}
	 * @see ErrorSeverityFinder::getSeverity()
	 */
	public function getSeverity($exception) {
		// do not report redirection transports or client errors
		if($exception instanceof SecurityPacket || $exception instanceof PathNotFoundException || $exception instanceof SecurityException) {
			return LOG_NONE;
		} else if($exception instanceof Error) {
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