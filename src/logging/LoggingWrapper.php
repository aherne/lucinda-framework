<?php
/**
 * Locates and instances loggers based on XML content.
 */
class LoggingWrapper {
	private $loggers = array();
	
	/**
	 * Reads XML tag loggers.{environment}, finds and saves loggers found.
	 *
	 * @param SimpleXMLElement $xml XML tag reference object.
	 */
	public function __construct(SimpleXMLElement $xml) {
		$this->setLoggers($xml);
	}
	
	/**
	 * Reads XML tag for loggers and saves them for later use.
	 * 
	 * @param SimpleXMLElement $xml
	 */
	private function setLoggers(SimpleXMLElement $xml) {
	    $xmlLoggers = (array) $xml;
	    foreach($xmlLoggers as $name=>$xmlProperties) {
	        switch($name) {
	            case "file":
	                require_once("loggers/FileLoggerWrapper.php");
	                $loggerWrapper = new FileLoggerWrapper($xmlProperties);
	                $this->loggers[] = $loggerWrapper->getLogger();
	                break;
	            case "syslog":
	                require_once("loggers/SysLoggerWrapper.php");
	                $loggerWrapper = new SysLoggerWrapper($xmlProperties);
	                $this->loggers[] = $loggerWrapper->getLogger();
                    break;
	            default:
	                require_once("loggers/CustomLoggerWrapper.php");
	                $loggerWrapper = new CustomLoggerWrapper($xmlProperties);
	                $this->loggers[] = $loggerWrapper->getLogger();
	                break;
	        }
	    }
	}
	
	/**
	 * Gets detected logger.
	 *
	 * @return Logger[] List of loggers found.
	 */
	public function getLoggers() {
		return $this->loggers;
	}
}