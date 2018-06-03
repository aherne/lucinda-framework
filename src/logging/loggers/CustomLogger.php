<?php
/**
 * Implements an abstract custom logger (one other than those supplied by Logging API) based on XML settings
 */
abstract class CustomLogger extends Logger {
    protected $settings;
    
    /**
     * @param SimpleXMLElement $settings Attributes and values that setup custom logger.
     */
    public function __construct(SimpleXMLElement $settings) {
        $this->settings = $settings;
    }
}