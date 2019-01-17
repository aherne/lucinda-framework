<?php
require_once("vendor/lucinda/framework-engine/src/logging/LoggingBinder.php");

/**
 * Binds STDOUT MVC with Logging API and contents of 'loggers' tag @ configuration.xml based on development environment
 * in order for developers to be able to log a message to a provider (eg: syslog).
 * 
 * Sets attributes:
 * - logger: (Lucinda\Framework\MultiLogger) encapsulated logger(s) detected, able to distribute message to all loggers registered
 */
class LoggingListener extends \Lucinda\MVC\STDOUT\ApplicationListener {
    /**
     * {@inheritDoc}
     * @see \Lucinda\MVC\STDOUT\Runnable::run()
     */
	public function run() {
	    $binder = new Lucinda\Framework\LoggingBinder($this->application->getTag("loggers"), ENVIRONMENT);
	    $this->application->attributes()->set("logger", $binder->getLogger());
	}
}