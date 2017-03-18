<?php
require_once("BugInformation.php");

class NoSQLLogger extends DataSourceLogger {
	private $connection;
	private $parameterName;
	private $rotationPattern;
	
	public function __construct($parameterName = "errors", NoSQLConnection $connection, $rotationPattern="Y_m_d") {
		$this->connection = $connection;
		$this->parameterName = $parameterName;
		$this->rotationPattern = $rotationPattern;
	}
	
	public function save(BugEnvironment $environment, Exception $exception) {
		try {
			$parameterName = $this->parameterName.($this->rotationPattern?"__".date($this->rotationPattern):"");
			$bugInformation = new BugInformation();
			$bugInformation->environment = $environment;
			$bugInformation->exception = $exception;
			$bugInformation->time = microtime(true);
			if($this->connection->contains($parameterName)) {
				$bugs = unserialize($this->connection->get($parameterName));
				$bugs[]=$bugInformation;
				$this->connection->set($parameterName, serialize($bugs));
			} else {
				$bugs = array();
				$bugs[]=$bugInformation;
				$this->connection->add($parameterName, serialize($bugs));
			}
		} catch(Exception $e) {
			// handling exceptions in exception handlers is disabled
		}
	}
}