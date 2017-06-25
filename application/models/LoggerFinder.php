<?php
/**
 * Locates and instances loggers based on XML content.
 */
class LoggerFinder {
	private $xml;
	
	/**
	 * Saves XML tag that contains loggers.
	 * 
	 * @param SimpleXMLElement $parent
	 */
	public function __construct(SimpleXMLElement $parent) {
		$this->xml = $parent;
	}
	
	/**
	 * Finds and instances loggers in container XML tag.
	 * 
	 * @return Logger[] List of loggers found.
	 */
	public function getLoggers() {
		$output = array();
		// check file reporting
		if($this->xml->file) {
			$output[] = $this->getFileLogger($this->xml->file);
		}
		
		// check syslog
		if($this->xml->syslog) {
			$output[] = $this->getSysLogger($this->xml->syslog);
		}
		
		// check sql logger
		if($this->xml->sql) {
			$output[] = $this->getSQLLogger($this->xml->sql);
		}
		
		return $output;
	}
	
	
	/**
	 * Constructs and returns instance of FileLogger based on XML content.
	 *
	 * @param SimpleXMLElement $xml XML settings for file logger.
	 * @throws ApplicationException On invalid XML content.
	 * @return Logger
	 */
	private function getFileLogger(SimpleXMLElement $xml) {
		require_once("libraries/php-logging-api/src/FileLogger.php");
		
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
		require_once("libraries/php-logging-api/src/SysLogger.php");
		
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
		require_once("libraries/php-logging-api/src/SQLLogger.php");
		
		if(!class_exists("SQLConnectionFactory")) {
			throw new ApplicationException("SQLDataSourceInjector listener has not ran!");
		}
		
		$serverName = (string) $xml["server"];
		$tableName = (string) $xml["table"];
		if(!$tableName) {
			throw new ApplicationException("Property 'table' missing in configuration.xml tag: errors.handlers.{environment}.reporters.sql!");
		}
		$output[] = new SQLLogger($tableName, (string) $xml["rotation"], ($serverName?SQLConnectionFactory::getInstance($serverName):SQLConnectionSingleton::getInstance()));
	}
}