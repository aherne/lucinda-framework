<?php
/**
 * Defines a blueprint all custom error reporters will have to extend.
 */
abstract class CustomReporter implements ErrorReporter {
	private $settings;
	
	/**
	 * Creates an object
	 * 
	 * @param SimpleXMLElement $settings Tag containing any settings necessary for the custom reporter.
	 */
	public function __construct(SimpleXMLElement $settings) {
		$this->settings = $settings;
	}
}