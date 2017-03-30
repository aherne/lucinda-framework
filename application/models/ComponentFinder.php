<?php
/**
 * Locates a custom component in configuration.xml
 */
class ComponentFinder {
	private $instance;
	
	/**
	 * Sets instance
	 * 
	 * @param SimpleXMLElement $xml Content of custom tag
	 * @param string $parentClassName Class custom reporter/renderer must extend.
	 * @param string $tagName Path of custom tag.
	 * @throws ApplicationException If XML structure is invalid
	 */
	public function __construct(SimpleXMLElement $xml, $parentClassName, $tagName) {
		$this->setComponent($xml, $parentClassName, $tagName);
	}
	
	/**
	 * Sets instance of component based on xml tag and parent class name
	 *
	 * @param SimpleXMLElement $xml Content of custom tag
	 * @param string $parentClassName Class custom reporter/renderer must extend.
	 * @param string $tagName Path of custom tag.
	 * @throws ApplicationException If XML structure is invalid
	 * @return ErrorReporter|ErrorRenderer
	 */
	private function setComponent(SimpleXMLElement $xml, $parentClassName, $tagName) {
		$class = (string) $xml["class"];
		if(!$class) {
			throw new ApplicationException("Property 'class' missing in configuration.xml tag: ".$tagName."!");
		}
		if(!file_exists(__DIR__."/".$class.".php")) {
			throw new ApplicationException("File could not be located on disk: ".__DIR__."/".$class.".php"."!");
		}
		require_once(__DIR__."/".$class.".php");
		if(!class_exists($class)) {
			throw new ApplicationException("Class not found: ".$class);
		}
		if(!is_subclass_of($class, $parentClassName)) {
			throw new ApplicationException($class." must be a subclass of ".$parentClassName."!");
		}
		$this->instance = new $class($xml);
	}
	
	/**
	 * Gets instance of component found.
	 * 
	 * @return object
	 */
	public function getComponent() {
		return $this->instance;
	}
}