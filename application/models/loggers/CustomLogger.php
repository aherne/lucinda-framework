<?php
/**
 * Defines a blueprint all custom loggers will have to extend.
 */
abstract class CustomLogger extends Logger {
	private $settings;
	
	/**
	 * Creates an object
	 * 
	 * @param SimpleXMLElement $settings Tag containing any settings necessary for the custom logger.
	 */
	public function __construct(SimpleXMLElement $settings) {
		$this->settings = $settings;
	}
}