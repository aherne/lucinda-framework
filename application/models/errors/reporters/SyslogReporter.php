<?php
require_once("LogReporter.php");

class SyslogReporter extends LogReporter {
    protected function getLogger(SimpleXMLElement $xml) {
        require_once(dirname(dirname(dirname(dirname(__DIR__))))."/vendor/lucinda/logging/src/SysLogger.php");
        
        $applicationName = (string) $xml["application"];
        if(!$applicationName) {
            throw new Lucinda\MVC\STDOUT\XMLException("Attribute 'path' is mandatory for 'syslog' tag");
        }
        
        $pattern= (string) $xml["format"];
        if(!$pattern) {
            throw new Lucinda\MVC\STDOUT\XMLException("Attribute 'format' is mandatory for 'syslog' tag");
        }
        
        return new Lucinda\Logging\SysLogger($applicationName, new Lucinda\Logging\LogFormatter($pattern));
    }
}