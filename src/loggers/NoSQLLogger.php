<?php
require_once("BugInformation.php");

/**
 * Logs errors into nosql databases.
 */
class NoSQLLogger extends DataSourceLogger {
	private $connection;
	private $parameterName;
	private $rotationPattern;
	
	/**
	 * Creates a logger instance.
	 * @param string $parameterName Name of parameter in which errors will be saved into.
	 * @param NoSQLConnection $connection Connection that's going to be used for saving errors.
	 * @param string $rotationPattern PHP date function format by which parameters will rotate.
	 */
	public function __construct($parameterName = "errors", NoSQLConnection $connection, $rotationPattern="Y_m_d") {
		$this->connection = $connection;
		$this->parameterName = $parameterName;
		$this->rotationPattern = $rotationPattern;
	}
	
	/**
	 * {@inheritDoc}
	 * @see DataSourceLogger::save()
	 */
	protected function save(BugEnvironment $environment, Exception $exception) {
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