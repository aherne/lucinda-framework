<?php
require_once("AbstractLoggerWrapper.php");

/**
 * Detects and constructs instance of FileLogger based on XML content.
 */
class FileLoggerWrapper extends AbstractLoggerWrapper {
    protected function setLogger(SimpleXMLElement $xml) {
        require_once("vendor/lucinda/logging/src/FileLogger.php");
        
        $filePath = (string) $xml["path"];
        if(!$filePath) {
            throw new ApplicationException("Property 'path' missing in configuration.xml tag: file!");
        }
        
        $pattern= (string) $xml["format"];
        if(!$pattern) {
            throw new ApplicationException("Property 'format' missing in configuration.xml tag: file!");
        }
        
        $this->logger = new FileLogger($filePath, (string) $xml["rotation"], new LogFormatter($pattern));
    }
}