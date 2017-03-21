<?php
require_once("libraries/php-errors-api/loader.php");

class ErrorListener extends ApplicationListener {
	const DEFAULT_LOG_FILE = "errors";
	
	public function run() {
		$errorHandler = new ErrorHandler();
		$reporters = $this->getReporters();
		foreach($reporters as $reporter) {
			$errorHandler->addReporter($reporter);
		}
		$errorHandler->setRenderer($this->getRenderer());
		PHPException::setErrorHandler($errorHandler);
		set_exception_handler(array($errorHandler,"handle"));
	}
	
	private function getReporters() {
		$output = array();
		
		// look for content of reporter tag
		$environment = $this->application->getAttribute("environment");
		$xml = $this->application->getXML()->errors->$environment;
		if(empty($xml) || empty($xml->reporters)) {
			// if no reporters were defined, use default ones
			require_once("src/loggers/FileLogger.php");
			$output[] = new FileLogger(self::DEFAULT_LOG_FILE);
			return $output;
		}
		$reporters = $xml->reporters;
		
		// compile output
		if($reporters->file) {
			require_once("src/loggers/FileLogger.php");
			$filePath = (string) $reporters->file->path;
			if(!$filePath) throw new ApplicationException("Property 'path' missing in configuration.xml tag: errors.{environment}.reporters.file!");
			$output[] = new FileLogger($filePath, (string) $reporters->file->rotation);
		}
		if($reporters->syslog) {
			require_once("src/loggers/SysLogger.php");
			$applicationName = (string) $reporters->syslog->application;
			if(!$applicationName) throw new ApplicationException("Property 'application' missing in configuration.xml tag: errors.{environment}.reporters.syslog!");
			$output[] = new SysLogger($applicationName);
		}
		if($reporters->sql) {
			require_once("src/loggers/SQLLogger.php");
			$serverName = (string) $reporters->sql->server;
			$output[] = new SQLLogger(
					(string) $reporters->sql->table,
					($serverName?SQLConnectionFactory::getInstance($serverName):SQLConnectionSingleton::getInstance()), 
					(string) $reporters->sql->rotation);
		}
		if($reporters->nosql) {
			require_once("src/loggers/NoSQLLogger.php");
			$serverName = (string) $reporters->nosql->server;
			$output[] = new SQLLogger(
					(string) $reporters->nosql->parameter,
					($serverName?NoSQLConnectionFactory::getInstance($serverName):NoSQLConnectionSingleton::getInstance()), 
					(string) $reporters->nosql->rotation);
		}
		
		return $output;
	}
	
	private function getRenderer() {
		// get extension
		$extension = $this->application->getDefaultExtension();
		$pathRequested = str_replace("?".$_SERVER["QUERY_STRING"],"",$_SERVER["REQUEST_URI"]);
		$dotPosition = strrpos($pathRequested,".");
		if($dotPosition!==false) {
			$extension = strtolower(substr($pathRequested,$dotPosition+1));
		}
		
		// compile output
		$environment = $this->application->getAttribute("environment");
		$xml = $this->application->getXML()->errors->$environment;
		if(empty($xml) || empty($xml->renderer)) {
			
		}
	}
}