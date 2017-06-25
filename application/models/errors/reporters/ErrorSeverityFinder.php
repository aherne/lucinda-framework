<?php
/**
 * Blueprint for error severity inspection.
 */
interface ErrorSeverityFinder {
	/**
	 * Gets error syslog severity.
	 *
	 * @param Throwable|Exception $exception
	 * @return integer Log severity constants values.
	 */
	function getSeverity($exception);
}