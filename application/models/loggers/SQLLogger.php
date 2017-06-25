<?php
/**
 * Logs messages/errors into SQL database. Requires a table with this structure (example @ MySQL):
 *
 CREATE TABLE {NAME}
 (
 id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
 uid CHAR(32) NOT NULL,
 level TINYINT UNSIGNED NOT NULL,
 url VARCHAR({SIZE}) NOT NULL,
 type VARCHAR({SIZE}) NOT NULL,
 file VARCHAR({SIZE}) NOT NULL,
 line SMALLINT UNSIGNED NOT NULL,
 message TEXT NOT NULL,
 environment TEXT NOT NULL,
 trace TEXT NOT NULL,
 date_added TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
 primary key(id),
 key(uid)
 ) Engine=MyISAM DEFAULT CHARSET=utf8;
 */
class SQLLogger extends Logger {
	const LOGGING_TYPE = "Log";
	
	private $statement;
	
	/**
	 * Creates a logger instance.
	 * @param string $tableName Name of table in which logs will be saved into.
	 * @param string $rotationPattern PHP date function format by which tables will rotate.
	 * @param SQLConnection $connection Connection that's going to be used for saving logs.
	 */
	public function __construct($tableName, $rotationPattern="Y_m_d", SQLConnection $connection) {
		$this->statement = $connection->createPreparedStatement();
		$this->statement->prepare("
		INSERT INTO ".$tableName.($rotationPattern?"__".date($rotationPattern):"")."
			(uid, level, url, type, file, line, message, environment, trace)
        VALUES
			(:uid, :level, :url, :type, :file, :line, :message, :environment, :trace)");
	}
	
	/**
	 * Gets environment of logging.
	 *
	 * @return array
	 */
	private function getEnvironmentInfo() {
		$environment = array();
		$environment["get"] = (!empty($_GET)?$_GET:array());
		$environment["post"] = (!empty($_POST)?$_POST:array());
		$environment["server"] = (!empty($_SERVER)?$_SERVER:array());;
		$environment["files"] = (!empty($_FILES)?$_FILES:array());;
		$environment["cookies"] = (!empty($_COOKIE)?$_COOKIE:array());;
		$environment["session"] = (!empty($_SESSION)?$_SESSION:array());;
		return $environment;
	}
	
	/**
	 * Strips trace of anything but file and line.
	 *
	 * @param array $trace PHP-formatted trace
	 * @return array Simplified trace
	 */
	private function stripTrace($trace) {
		$output = array();
		foreach($trace as $item) {
			if(empty($item['file'])) continue; // for fatal errors there is no trace
			$output[]=array("file"=>$item["file"],"line"=>$item["line"]);
		}
		return $output;
	}
	
	/**
	 * {@inheritDoc}
	 * @see Logger::log()
	 */
	protected function log($info, $level) {
		try {
			// create parameter bindings
			$params = array();
			$params[":url"] = $_SERVER['REQUEST_URI'];
			$params[":environment"] = serialize($this->getEnvironmentInfo());
			$params[":level"] = $level;
			if($info instanceof Exception || $info instanceof Throwable) {
				$params[":type"]=get_class($info);
				$params[":file"]=$info->getFile();
				$params[":line"]=$info->getLine();
				$params[":message"]=$info->getMessage();
				$params[":trace"]=serialize($this->stripTrace($info->getTrace()));
			} else {
				$trace = debug_backtrace();
				unset($trace[0]);
				$params[":type"]=self::LOGGING_TYPE;
				$params[":file"]=$trace[1]["file"];
				$params[":line"]=$trace[1]["line"];
				$params[":message"]=$info;
				$params[":trace"]=serialize($this->stripTrace($trace));
			}
			$params[":uid"] = md5($params[":url"]."#".$params[":type"]."#".$params[":file"]."#".$params[":line"]."#".$params[":message"]);
			
			// execute prepared statement with bound parameters
			$this->statement->execute($params);
		} catch(Exception $e) {}
	}
}