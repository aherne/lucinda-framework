<?php
require_once("vendor/lucinda/logging/loader.php");
require_once("application/models/LoggerFinder.php");
require_once("src/MultiLogger.php");

/**
 * Sets up logging in your application by binding  PHP-LOGGING-API with contents of "loggers" tag @ CONFIGURATION.XML, itself handled by SERVLETS API.  
 * 
 * Syntax for "logging" XML tag is:
 * <loggers>
 * 		<{ENVIRONMENT_NAME}>
 * 			...
 * 		</{ENVIRONMENT_NAME}>
 * </loggers>
 * 
 * Because behavior depends on environment, this listener requires EnvironmentDetector to be ran beforehand. First logger identified in loggers.{ENVIRONMENT} tag  
 * will be the one made available across application as "logger" application attribute.
 * 
 * @attribute logger
 */
class LoggingListener extends ApplicationListener {
	const DEFAULT_LOG_FILE = "logs";

	/**
	 * {@inheritDoc}
	 * @see Runnable::run()
	 */
	public function run() {
		$this->application->setAttribute("logger", $this->getLogger());
	}
	
	/**
	 * Finds logger among children of loggers.{ENVIRONMENT} tag. Following children are recognized:
	 * 		<file path="{FILE_PATH}" rotation="{ROTATION_PATTERN}"/>
	 * 		<syslog application="{APPLICATION_NAME}"/>
	 * 		<sql table="{TABLE_NAME}" server="{SERVER_NAME}" rotation="{ROTATION_PATTERN}"/>
	 * 		<logger class="{CLASS}" .../>
	 * 
	 * Where:
	 * - "file": logging is done in a file on your server's disk
	 * - "syslog": logging is done via syslog service running on your server
	 * - "logger": if you want to add a custom reporter (class must extend CustomLogger class)
	 * 
	 * If no logger is defined, no logging will be made
	 * 
	 * @throws ApplicationException On invalid XML content.
	 * @return MultiLogger|null
	 */
	private function getLogger() {
		// look for container tag
		$environment = $this->application->getAttribute("environment");
		$xml = $this->application->getXML()->loggers;
		if(empty($xml) || empty($xml->$environment)) {
			return;
		}
		
		// find loggers and return a global wrapper
		$finder = new LoggerFinder($xml->$environment);
		$loggers = $finder->getLoggers();
		if(empty($loggers)) {
			return;
		} else {
			return new MultiLogger($loggers);
		}			
	}
}