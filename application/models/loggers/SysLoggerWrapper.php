<?php
/**
 * Detects and constructs instance of FileLogger based on XML content.
 */
class SysLoggerWrapper extends Lucinda\Framework\AbstractLoggerWrapper {
    protected function setLogger(SimpleXMLElement $xml) {
        require_once("vendor/lucinda/logging/src/SysLogger.php");
        
        $applicationName = (string) $xml["application"];
        if(!$applicationName) {
            throw new Lucinda\MVC\STDOUT\XMLException("Property 'path' missing in configuration.xml tag: syslog!");
        }
        
        $pattern= (string) $xml["format"];
        if(!$pattern) {
            throw new Lucinda\MVC\STDOUT\XMLException("Property 'format' missing in configuration.xml tag: syslog!");
        }
        
        $this->logger = new Lucinda\Logging\SysLogger($applicationName, new Lucinda\Logging\LogFormatter($pattern));
    }
}