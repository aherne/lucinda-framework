<?php
require_once("BugEnvironment.php");

/**
 * Reports errors in nosql/sql data sources
 */
abstract class DataSourceReporter implements ErrorReporter {
	/**
	 * Collects environment information into an object.
	 * 
	 * @return BugEnvironment
	 */
	protected function getEnvironmentInformation() {
		$environment = new BugEnvironment();
		$environment->get = $_GET;
		$environment->post = $_POST;
		$environment->server = $_SERVER;
		$environment->files = $_FILES;
		$environment->cookies = $_COOKIE;
		$environment->session = $_SESSION;
		return $environment;
	}
}