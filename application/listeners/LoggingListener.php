<?php
/**
 * Sets up customized logging by connecting PHP-LOGGING-API with CONFIGURATION.XML @ SERVLETS API, after EnvironmentListener has ran. 
 * Sets a "logger" application attribute that hides loggers complexity. Reads XML "loggers" tag for sub-tags, per detected environment:
 * 
 * Syntax of 
 * <loggers>
 * 		<{ENVIRONMENT_NAME}>
 * 			...
 * 		</{ENVIRONMENT_NAME}>
 * </loggers>
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
	 * 
	 * @throws ApplicationException
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