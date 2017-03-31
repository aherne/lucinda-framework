<?php
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
	 * - "sql": logging is done into an sql table
	 * - "logger": if you want to add a custom reporter (class must extend CustomLogger class)
	 * 
	 * If no logger is defined, no logging will be made
	 * 
	 * @throws ApplicationException On invalid XML content.
	 * @return Logger|null
	 */
	private function getLogger() {
		// look for reporters tag
		$environment = $this->application->getAttribute("environment");
		$xml = $this->application->getXML()->loggers;
		if(empty($xml) || empty($xml->$environment)) {
			return;
		}
		$loggers = $xml->$environment;
		
		// check file reporting
		if($loggers->file) {
			require_once("libraries/php-logging-api/src/FileLogger.php");
		
			$filePath = (string) $loggers->file["path"];
			if(!$filePath) {
				throw new ApplicationException("Property 'path' missing in configuration.xml tag: loggers.{environment}.file!");
			}
			return new FileLogger($filePath, (string) $loggers->file["rotation"]);
		}
		
		// check syslog
		if($loggers->syslog) {
			require_once("libraries/php-logging-api/src/SysLogger.php");
		
			$applicationName = (string) $loggers->syslog["application"];
			if(!$applicationName) {
				throw new ApplicationException("Property 'application' missing in configuration.xml tag: loggers.{environment}.syslog!");
			}
			return new SysLogger($applicationName);
		}
		
		// check sql
		if($loggers->sql) {
			require_once("libraries/php-logging-api/src/SQLLogger.php");
		
			$serverName = (string) $loggers->sql["server"];
			if(!class_exists("SQLConnectionFactory")) {
				throw new ApplicationException("SQLDataSourceInjector listener has not ran!");
			}
			$tableName = (string) $loggers->sql["table"];
			if(!$tableName) {
				throw new ApplicationException("Property 'table' missing in configuration.xml tag: loggers.{environment}.sql!");
			}
			return new SQLLogger($tableName, ($serverName?SQLConnectionFactory::getInstance($serverName):SQLConnectionSingleton::getInstance()), (string) $loggers->sql["rotation"]);
		}
		
		// check custom logger
		if($loggers->logger) {
			require_once("libraries/php-logging-api/src/Logger.php");
			require_once("application/models/loggers/CustomLogger.php");
			require_once("application/models/ComponentFinder.php");
			
			$componentFinder = new ComponentFinder($loggers->logger, "CustomLogger", "loggers.{environment}.logger");
			return $componentFinder->getComponent();
		}
		
	}
}