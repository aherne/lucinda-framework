<?php
require_once("PersistenceDriverWrapper.php");

/**
 * Binds SessionPersistenceDriver @ SECURITY API with settings from configuration.xml @ SERVLETS-API and sets up an object on which one can
 * forward session persistence operations.
 */
class SessionPersistenceDriverWrapper extends PersistenceDriverWrapper {
	const DEFAULT_PARAMETER_NAME = "uid";
	const HANDLER_FOLDER = "application/models";

	/**
	 * {@inheritDoc}
	 * @see PersistenceDriverWrapper::setDriver()
	 */
	protected function setDriver(SimpleXMLElement $xml) {
		$parameterName = (string) $xml["parameter_name"];
		if(!$parameterName) $parameterName = self::DEFAULT_PARAMETER_NAME;

		$expirationTime = (integer) $xml["expiration"];
		$isHttpOnly = (integer) $xml["is_http_only"];
		$isHttpsOnly = (integer) $xml["is_https_only"];
		$ip = ((string) $xml["ignore_ip"]?"":$_SERVER["REMOTE_ADDR"]);
		
		$handler = (string) $xml["handler"];
		if($handler) {
		    session_set_save_handler($this->getHandlerInstance($handler), true);
		}
		
		$this->driver = new SessionPersistenceDriver($parameterName, $expirationTime, $isHttpOnly, $isHttpsOnly, $ip);
	}
	
	/**
	 * Gets instance of handler based on handler name
	 * 
	 * @param string $handlerName Name of handler class
	 * @throws ServletException If handler file/class not found or latter is not instanceof SessionHandlerInterface
	 * @return SessionHandlerInterface
	 */
	private function getHandlerInstance($handlerName) {
	    $file = self::HANDLER_FOLDER."/".$handlerName.".php";
	    if(!file_exists($file)) throw new ServletException("Handler file not found: ".$file);
	    require_once($file);
	    if(!class_exists($handlerName)) throw new ServletException("Handler class not found: ".$handlerName);
	    $object = new $handlerName();
	    if(!($object instanceof SessionHandlerInterface))  throw new ServletException("Handler must be instance of SessionHandlerInterface!");
	    return $object;
	}
}