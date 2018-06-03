<?php
require_once("AbstractLoggerWrapper.php");
require_once("CustomLogger.php");

/**
 * Detects and constructs instance of custom logger based on XML content.
 */
class CustomLoggerWrapper extends AbstractLoggerWrapper {
    protected function setLogger(SimpleXMLElement $xml) {
        // detect class name
        $className = (string) $xml["class"];
        if(!$className) {
            throw new ApplicationException("Property 'class' missing in configuration.xml custom logger!");
        }
        
        // detect class path
        $path = (string) $xml["path"];
        if(!$path) {
            throw new ApplicationException("Property 'path' missing in configuration.xml custom logger!");
        }
        
        // load class file
        $fileName = $path."/".$className.".php";
        if(!file_exists($fileName)) {
            throw new ServletException("Logger could not be found on disk!");
        }
        require_once($fileName);
        
        if(!class_exists($className)) {
            throw new ServletException("Class could not be found: ".$className."!");
        }
        
        $object = new $className($xml);
        
        if(!($object instanceof CustomLogger)) {
            throw new ApplicationException("Class must be instance of CustomLogger: ".$className."!");
        }
        
        $this->logger = $object;
    }
}