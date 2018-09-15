<?php
require_once("vendor/lucinda/errors-mvc/src/ErrorReporter.php");
require_once("vendor/lucinda/errors-mvc/src/Request.php");
require_once("vendor/lucinda/errors-mvc/src/ErrorType.php");

/**
 * Binds Errors MVC API to Logging API in order to report errors through loggers.
 */
abstract class LogReporter implements Lucinda\MVC\STDERR\ErrorReporter {
	private $logger;
	
	/**
	 * Detects
	 * 
	 * @param SimpleXMLElement $xml Contents of reporter tag @ errors document descriptor XML
	 */
	public function __construct(SimpleXMLElement $xml) {
	    // reads xml and sets up a logger instance
	    $this->logger = $this->getLogger($xml);
	}
	
	/**
	 * Detects logger based on XML attributes.
	 * 
	 * @param SimpleXMLElement $xml Contents of reporter tag @ errors document descriptor XML
	 * @return Lucinda\Logging\Logger
	 */
	abstract protected function getLogger(SimpleXMLElement $xml);
	
	/**
	 * {@inheritDoc}
	 * @see \Lucinda\MVC\STDERR\ErrorReporter::report()
	 */
	public function report(Lucinda\MVC\STDERR\Request $request) {
		switch($request->getRoute()->getErrorType()) {
		    case Lucinda\MVC\STDERR\ErrorType::NONE:
		    case Lucinda\MVC\STDERR\ErrorType::CLIENT:
				break;
		    case Lucinda\MVC\STDERR\ErrorType::SERVER:
			    $this->logger->emergency($request->getException());
				break;
		    case Lucinda\MVC\STDERR\ErrorType::SYNTAX:
			    $this->logger->alert($$request->getException());
				break;
		    case Lucinda\MVC\STDERR\ErrorType::LOGICAL:
			    $this->logger->critical($request->getException());
				break;
			default:
			    $this->logger->error($request->getException());
				break;			
		}
	}
}

