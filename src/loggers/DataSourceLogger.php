<?php
require_once("BugEnvironment.php");

abstract class DataSourceLogger implements ErrorReporter {
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
	
	abstract protected function save(BugEnvironment $environment, Exception $exception);
}