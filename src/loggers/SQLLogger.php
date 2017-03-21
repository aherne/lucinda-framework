<?php
require_once("BugEnvironment.php");
require_once("DataSourceLogger.php");

/**
 * Logs errors into sql databases.
 */
class SQLLogger extends DataSourceLogger {
	private $connection;
	private $tableName;
	private $rotationPattern;

	/**
	 * Creates a logger instance.
	 * @param string $tableName Name of parameter in which errors will be saved into.
	 * @param SQLConnection $connection Connection that's going to be used for saving errors.
	 * @param string $rotationPattern PHP date function format by which tables will rotate.
	 */
	public function __construct($tableName = "errors", SQLConnection $connection, $rotationPattern="Y_m_d") {
		$this->connection = $connection;
		$this->tableName = $tableName;
		$this->rotationPattern = $rotationPattern;
	}
	
	/**
	 * {@inheritDoc}
	 * @see DataSourceLogger::save()
	 */
	protected function save(BugEnvironment $environment, Exception $exception) {
		$tableName = $this->tableName.($this->rotationPattern?"__".date($this->rotationPattern):"");
		try {
			$preparedStatement = $this->connection->createPreparedStatement();
			$preparedStatement->prepare("
            INSERT INTO ".$tableName." 
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