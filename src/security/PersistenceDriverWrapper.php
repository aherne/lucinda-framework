<?php
/**
 * Defines an abstract persistence mechanism that works with PersistenceDriver objects.
 */
abstract class PersistenceDriverWrapper {
	protected $driver;
	
	/**
	 * Creates an object.
	 * 
	 * @param SimpleXMLElement $xml Contents of XML tag that sets up persistence driver.
	 */
	public function __construct(SimpleXMLElement $xml) {
		$this->setDriver($xml);
	}
	
	/**
	 * Sets up current persistence driver from XML into driver property.
	 * 
	 * @param SimpleXMLElement $xml Contents of XML tag that sets up persistence driver.
	 */
	abstract protected function setDriver(SimpleXMLElement $xml);
	
	/**
	 * Gets current persistence driver.
	 * 
	 * @return PersistenceDriver
	 */
	public function getDriver() {
		return $this->driver;
	}
}