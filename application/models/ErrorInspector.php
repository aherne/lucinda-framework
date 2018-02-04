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
	    $type = get_class($exception);	    
	    switch($type) {
	        case "PathNotFoundException":
	        case "MethodNotAllowedException":
	        case "SecurityPacket":
	        case "SessionHijackException":
	        case "EncryptionException":
	        case "TokenException":
	        case "FileUploadException":
	            return LOG_NONE;
	            break;
	        case "SQLConnectionException":
	        case "NoSQLConnectionException":
	        case "OAuth2\ServerException":
	            return LOG_EMERG;
	            break;
	        case "ApplicationException":
	        case "ServletException":
	        case "FormatNotFoundException":
	        case "SQLStatementException":
	        case "SQLException":
	        case "OperationFailedException":
	        case "KeyNotFoundException":
	        case "OAuth2\ClientException":
	        case "AuthenticationException":
	        case "AuthorizationException":
	        case "PHPException":
	            return LOG_CRIT;
	            break;
	        case "ViewException":
	            return LOG_ALERT;
	            break;
	        default:
	            return LOG_ERR;
	            break;
	    }
	}
}