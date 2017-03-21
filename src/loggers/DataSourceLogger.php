<?php
require_once("BugEnvironment.php");

/**
 * Abstracts error reporting into sql/nosql databases.
 */
abstract class DataSourceLogger implements ErrorReporter {
	/**
	 * {@inheritDoc}
	 * @see ErrorReporter::report()
	 */
	public function report(Exception $exception) {
		$environment = new BugEnvironment();
		$environment->get = $_GET;
		$environment->post = $_POST;
		$environment->server = $_SERVER;
		$environment->files = $_FILES;
		$environment->cookies = $_COOKIE;
		$environment->session = $_SESSION;
		$this->save($environment, $exception);
	}
	
	/**
	 * Stores error in database.
	 * 
	 * @param BugEnvironment $environment
	 * @param Exception $exception
	 */
	abstract protected function save(BugEnvironment $environment, Exception $exception);
}