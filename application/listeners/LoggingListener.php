<?php
require_once("vendor/lucinda/logging/loader.php");
require_once("src/logging/LoggingWrapper.php");
require_once("src/logging/MultiLogger.php");

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
	/**
	 * {@inheritDoc}
	 * @see Runnable::run()
	 */
	public function run() {
	    // look for container tag
	    $environment = $this->application->getAttribute("environment");
	    $xml = $this->application->getXML()->loggers;
	    if(empty($xml) || empty($xml->$environment)) {
	        return;
	    }
	    
	    // finds loggers and return a global wrapper
	    $finder = new LoggingWrapper($xml->$environment);
	    $loggers = $finder->getLoggers();
	    if(!empty($loggers)) {
	        $this->application->setAttribute("logger", new MultiLogger($loggers));
	    }					
	}
}