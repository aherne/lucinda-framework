<?php
require_once("LogReporter.php");

class SyslogReporter extends LogReporter {
    protected function getLogger(SimpleXMLElement $xml) {
        require_once("vendor/lucinda/logging/src/SysLogger.php");
        
        $applicationName = (string) $xml["application"];
        if(!$applicationName) {
            throw new ApplicationException("Property 'path' missing in configuration.xml tag: syslog!");
        }
        
        $pattern= (string) $xml["format"];
        if(!$pattern) {
            throw new ApplicationException("Property 'format' missing in configuration.xml tag: syslog!");
        }
        
        return new SysLogger($applicationName, new LogFormatter($pattern));
    }
}