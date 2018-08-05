<?php
require_once("LogReporter.php");

class FileReporter extends LogReporter {
    protected function getLogger(SimpleXMLElement $xml) {
        require_once("vendor/lucinda/logging/src/FileLogger.php");
        
        $filePath = (string) $xml["path"];
        if(!$filePath) {
            throw new ApplicationException("Property 'path' missing in configuration.xml tag: file!");
        }
        
        $pattern= (string) $xml["format"];
        if(!$pattern) {
            throw new ApplicationException("Property 'format' missing in configuration.xml tag: file!");
        }
        
        return new FileLogger($filePath, (string) $xml["rotation"], new LogFormatter($pattern));
    }
}