<?php
/**
 * Locates and instances loggers based on XML content.
 */
class LoggerFinder {
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
	 * Reads XML tag
	 * @param SimpleXMLElement $xml
	 */
	private function setLoggers(SimpleXMLElement $xml) {
		// check file reporting
		if($xml->file) {
			$this->loggers[] = $this->getFileLogger($xml->file);
		}
		
		// check syslog
		if($xml->syslog) {
			$this->loggers[] = $this->getSysLogger($xml->syslog);
		}
		
		// check sql logger
		if($xml->sql) {
			$this->loggers[] = $this->getSQLLogger($xml->sql);
		}
	}
	
	/**
	 * Finds and instances loggers in container XML tag.
	 *
	 * @return Logger[] List of loggers found.
	 */
	public function getLoggers() {
		return $this->loggers;
	}
	
	/**
	 * Constructs and returns instance of FileLogger based on XML content.
	 *
	 * @param SimpleXMLElement $xml XML settings for file logger.
	 * @throws ApplicationException On invalid XML content.
	 * @return Logger
	 */
	private function getFileLogger(SimpleXMLElement $xml) {
		require_once("vendor/lucinda/logging/src/FileLogger.php");
		
		$filePath = (string) $xml["path"];
		if(!$filePath) {
			throw new ApplicationException("Property 'path' missing in configuration.xml tag: file!");
		}
		
		$pattern= (string) $xml["format"];
		if(!$pattern) {
			throw new ApplicationException("Property 'format' missing in configuration.xml tag: file!");
		}
		
		return new FileLogger($filePath, (string) $xml["rotation"], new LogFormatter($pattern));
	}
	
	/**
	 * Constructs and returns instance of SysLogger based on XML content.
	 *
	 * @param SimpleXMLElement $xml XML settings for sys logger.
	 * @throws ApplicationException On invalid XML content.
	 * @return Logger
	 */
	private function getSysLogger(SimpleXMLElement $xml) {
		require_once("vendor/lucinda/logging/src/SysLogger.php");
		
		$applicationName = (string) $xml["application"];
		if(!$applicationName) {
			throw new ApplicationException("Property 'path' missing in configuration.xml tag: syslog!");
		}
		
		$pattern= (string) $xml["format"];
		if(!$pattern) {
			throw new ApplicationException("Property 'format' missing in configuration.xml tag: syslog!");
		}
		
		return new SysLogger($applicationName, new LogFormatter($pattern));
	}
	
	/**
	 * Constructs and returns instance of SQLLogger based on XML content.
	 *
	 * @param SimpleXMLElement $xml XML settings for sql logger.
	 * @throws ApplicationException On invalid XML content.
	 * @return Logger
	 */
	private function getSQLLogger(SimpleXMLElement $xml) {
		require_once("application/models/loggers/SQLLogger.php");
		
		if(!class_exists("SQLConnectionFactory")) {
			throw new ApplicationException("SQLDataSourceInjector listener has not ran!");
		}
		
		$serverName = (string) $xml["server"];
		$tableName = (string) $xml["table"];
		if(!$tableName) {
			throw new ApplicationException("Property 'table' missing in configuration.xml tag: errors.{environment}.reporters.sql!");
		}
		return new SQLLogger($tableName, (string) $xml["rotation"], ($serverName?SQLConnectionFactory::getInstance($serverName):SQLConnectionSingleton::getInstance()));
	}
}