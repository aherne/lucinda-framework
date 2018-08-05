<?php
/**
 * Detects and constructs instance of FileLogger based on XML content.
 */
class SysLoggerWrapper extends AbstractLoggerWrapper {
    protected function setLogger(SimpleXMLElement $xml) {
        require_once("vendor/lucinda/logging/src/SysLogger.php");
        
        $applicationName = (string) $xml["application"];
        if(!$applicationName) {
            throw new ApplicationException("Property 'path' missing in configuration.xml tag: syslog!");
        }
        
        $pattern= (string) $xml["format"];
        if(!$pattern) {
            throw new ApplicationException("Property 'format' missing in configuration.xml tag: syslog!");
        }
        
        $this->logger = new SysLogger($applicationName, new LogFormatter($pattern));
    }
}