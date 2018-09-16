<?php
require_once("LogReporter.php");

class FileReporter extends LogReporter {
    protected function getLogger(SimpleXMLElement $xml) {
        $rootFolder = dirname(dirname(dirname(dirname(__DIR__))));
        require_once($rootFolder."/vendor/lucinda/logging/src/FileLogger.php");
        
        $filePath = $rootFolder."/".$xml["path"];
        if(!$filePath) {
            throw new Lucinda\MVC\STDOUT\XMLException("Property 'path' missing in configuration.xml tag: file!");
        }
        
        $pattern= (string) $xml["format"];
        if(!$pattern) {
            throw new Lucinda\MVC\STDOUT\XMLException("Property 'format' missing in configuration.xml tag: file!");
        }
        
        return new Lucinda\Logging\FileLogger($filePath, (string) $xml["rotation"], new Lucinda\Logging\LogFormatter($pattern));
    }
}