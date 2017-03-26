<?php
/**
 * Defines a blueprint all custom error renderers will have to extend.
 */
abstract class CustomRenderer implements ErrorRenderer {
	private $settings;
	
	/**
	 * Creates an object
	 * 
	 * @param SimpleXMLElement $settings Tag containing any settings necessary for the custom renderer.
	 */
	public function __construct(SimpleXMLElement $settings) {
		$this->settings = $settings;
	}
}