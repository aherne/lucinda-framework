<?php
/**
 * Defines blueprints for error severity checks.
 */
interface SeverityFinder {
	/**
	 * Gets syslog error level based on exception.
	 * 
	 * @param Exception $exception
	 * @return integer Value of syslog error level (see: http://php.net/manual/ro/function.syslog.php)
	 */
	function getSeverity(Exception $exception);
}