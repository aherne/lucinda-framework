<?php
require_once("LogReporter.php");

class FileReporter extends LogReporter {
    protected function getLogger(SimpleXMLElement $xml) {
        $rootFolder = dirname(dirname(dirname(dirname(__DIR__))));
        require_once($rootFolder."/vendor/lucinda/logging/src/FileLogger.php");
        
        $filePath = $rootFolder."/".$xml["path"];
        if(!$filePath) {
            throw new Lucinda\MVC\STDOUT\XMLException("Attribute 'path' is mandatory for 'file' tag");
        }
        
        $pattern= (string) $xml["format"];
        if(!$pattern) {
            throw new Lucinda\MVC\STDOUT\XMLException("Attribute 'format' is mandatory for 'file' tag");
        }
        
        return new Lucinda\Logging\FileLogger($filePath, (string) $xml["rotation"], new Lucinda\Logging\LogFormatter($pattern));
    }
}