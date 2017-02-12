<?php
abstract class PersistenceDriverWrapper {
	protected $driver;
	
	public function __construct(SimpleXMLElement $xml) {
		$this->setDriver($xml);
	}
	
	abstract protected function setDriver(SimpleXMLElement $xml);
	
	public function getDriver() {
		return $this->driver;
	}
}