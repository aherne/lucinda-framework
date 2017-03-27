<?php
class ExceptionWrapper implements SeverityFinder {
	// TODO: public function getAuthor(Exception $exception) @ ErrorAuthor
	// TODO: rename SeverityFInder to ErrorSeverity
	public function getSeverity(Exception $exception) {
		if($exception instanceof PHPException) {
			return LOG_CRIT; 	// programmer fault
		} else if($exception instanceof NoSQLConnectionException) {
			return LOG_EMERG; 	// server fault
		} else if($exception instanceof NoSQLStatementException) {
			return LOG_CRIT; 	// programmer fault
		} else if($exception instanceof SQLConnectionException) {
			return LOG_EMERG;	// server fault
		} else if($exception instanceof SQLStatementException) {
			return LOG_CRIT; 	// programmer fault
		} else if($exception instanceof AuthenticationException) {
			return LOG_CRIT; 	// programmer fault
		} else if($exception instanceof SessionHijackException) {
			return LOG_ERR; 	// client fault (hacking attempt)
		} else if($exception instanceof EncryptionException) {
			return LOG_ERR; 	// client fault (hacking attempt)
		} else if($exception instanceof TokenException) {
			return LOG_ERR; 	// client fault (hacking attempt)
		} else if($exception instanceof ApplicationException) {
			return LOG_CRIT; 	// programmer fault
		} else if($exception instanceof FileUploadException) {
			return LOG_ERR; 	// client fault
		} else if($exception instanceof FormatNotFoundException) {
			return LOG_CRIT; 	// client fault
		} else if($exception instanceof PathNotFoundException) {
			return LOG_CRIT; 	// client fault
		} else if($exception instanceof ServletException) {
			return LOG_CRIT;	// programmer fault
		} else if($exception instanceof ViewException) {
			return LOG_ERR;		// programmer fault
		} else {
			return LOG_ERR;
		}
	}
}