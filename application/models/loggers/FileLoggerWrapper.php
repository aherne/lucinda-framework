<?php
/**
 * Detects and constructs instance of FileLogger based on XML content.
 */
class FileLoggerWrapper extends Lucinda\Framework\AbstractLoggerWrapper {
    protected function setLogger(SimpleXMLElement $xml) {
        require_once("vendor/lucinda/logging/src/FileLogger.php");
        
        $filePath = (string) $xml["path"];
        if(!$filePath) {
            throw new Lucinda\MVC\STDOUT\XMLException("Property 'path' missing in configuration.xml tag: file!");
        }
        
        $pattern= (string) $xml["format"];
        if(!$pattern) {
            throw new Lucinda\MVC\STDOUT\XMLException("Property 'format' missing in configuration.xml tag: file!");
        }
        
        $this->logger = new Lucinda\Logging\FileLogger($filePath, (string) $xml["rotation"], new Lucinda\Logging\LogFormatter($pattern));
    }
}