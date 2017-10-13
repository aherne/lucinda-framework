<?php
require_once("LogReporter.php");
require_once("application/models/LoggerFinder.php");
require_once("application/models/ErrorInspector.php");

/**
 * Locates and instances error reporters based on XML content.
 */
class ErrorReportersFinder {
	protected $reporters = array();
	
	/**
	 * Reads XML tag errors.{environment}.reporters, then finds and saves reporters found.
	 *
	 * @param SimpleXMLElement $xml XML tag reference object.
	 * @param string $environment Current development environment.
	 */
	public function __construct(SimpleXMLElement $xml, $environment) {
		if(empty($xml) || empty($xml->$environment) || empty($xml->$environment->reporters)) {
			return;
		}
		$this->setReporters($xml->$environment->reporters);
	}
	
	/**
	 * Finds loggers in container XML tag, wraps them into LogReporter instances then finally saves them for latter reference.
	 * 
	 * @param SimpleXMLElement $xml Contents of errors.{environment}.reporters tag.
	 */
	protected function setReporters(SimpleXMLElement $xml) {
		$esf = new ErrorInspector();
		$finder = new LoggerFinder($xml);
		$loggers  = $finder->getLoggers();
		foreach($loggers as $logger) {
			$this->reporters[] = new LogReporter($logger, $esf);
		}
	}
	
	
	/**
	 * Gets error reporters found.
	 *
	 * @return ErrorReporter[] List of error reporters found.
	 */
	public function getReporters() {
		return $this->reporters;
	}
}