<?php
require_once("LogReporter.php");

class SyslogReporter extends LogReporter {
    protected function getLogger(SimpleXMLElement $xml) {
        require_once("vendor/lucinda/logging/src/SysLogger.php");
        
        $applicationName = (string) $xml["application"];
        if(!$applicationName) {
            throw new Lucinda\MVC\STDOUT\XMLException("Property 'path' missing in configuration.xml tag: syslog!");
        }
        
        $pattern= (string) $xml["format"];
        if(!$pattern) {
            throw new Lucinda\MVC\STDOUT\XMLException("Property 'format' missing in configuration.xml tag: syslog!");
        }
        
        return new Lucinda\Logging\SysLogger($applicationName, new Lucinda\Logging\LogFormatter($pattern));
    }
}