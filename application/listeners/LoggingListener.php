<?php
require_once("vendor/lucinda/logging/loader.php");
require_once("vendor/lucinda/framework-engine/src/logging/LoggingBinder.php");

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
class LoggingListener extends Lucinda\MVC\STDOUT\ApplicationListener {
	/**
	 * {@inheritDoc}
	 * @see Runnable::run()
	 */
	public function run() {
	    $binder = new LoggingBinder($this->application);
	    $this->application->attributes()->set("logger", $binder->getLogger());
	}
}