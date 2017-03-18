<?php
require_once("BugEnvironment.php");

class SQLLogger extends DataSourceLogger {
	private $connection;
	private $tableName;
	private $rotationPattern;
	
	public function __construct($tableName = "errors", SQLConnection $connection, $rotationPattern="Y_m_d") {
		$this->connection = $connection;
		$this->tableName = $tableName;
		$this->rotationPattern = $rotationPattern;
	}
	
	public function save(BugEnvironment $environment, Exception $exception) {
		try {
			$preparedStatement = $this->connection->createPreparedStatement();
			$preparedStatement->prepare("
            INSERT INTO ".$this->tableName.($this->rotationPattern?"__".date($this->rotationPattern):"")." 
				(type, file, line, message, environment, exception))
            VALUES (:type, :file, :line, :message, :environment, :exception)");
			$preparedStatement->bind(":type", get_class($exception));
			$preparedStatement->bind(":file", $exception->getFile());
			$preparedStatement->bind(":line", $exception->getLine(), PDO::PARAM_INT);
			$preparedStatement->bind(":message", $exception->getMessage());
			$preparedStatement->bind(":environment", serialize($environment));
			$preparedStatement->bind(":exception", serialize($exception));
			$preparedStatement->execute();
		} catch(Exception $e) {
			// handling exceptions in exception handlers is disabled
		}
	}
}