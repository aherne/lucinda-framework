<?php
require_once("BugEnvironment.php");

/**
 * Reports errors into an sql table with this structure:
 * 
 * @MySQL:
 * 
 * CREATE TABLE {TABLE_NAME}(__{Y_M_D}) 
 * 	(
 * 	id bigint unsigned not null auto_increment,
 * 	file text not null,
 *	line int unsigned not null,
 * 	message text not null,
 * 	environment text not null,
 * 	exception text not null,
 * 	date_added timestamp not null default current_timestamp,
 * 	primary key(id),
 * 	key(date)
 * ) Engine=MyISAM CHARACTER SET utf8;
 */
class SQLReporter implements ErrorReporter {
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
	 * @see ErrorReporter::report()
	 */
	protected function report(Exception $exception) {
		// collect environment information
		$environment = new BugEnvironment();
		$environment->get = $_GET;
		$environment->post = $_POST;
		$environment->server = $_SERVER;
		$environment->files = $_FILES;
		$environment->cookies = $_COOKIE;
		$environment->session = $_SESSION;
		
		// write to table
		try {
			$tableName = $this->tableName.($this->rotationPattern?"__".date($this->rotationPattern):"");
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