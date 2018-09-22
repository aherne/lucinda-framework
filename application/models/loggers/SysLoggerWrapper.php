<?php
/**
 * Detects and constructs instance of FileLogger based on XML content.
 */
class SysLoggerWrapper extends Lucinda\Framework\AbstractLoggerWrapper {
    protected function setLogger(SimpleXMLElement $xml) {
        require_once("vendor/lucinda/logging/src/SysLogger.php");
        
        $applicationName = (string) $xml["application"];
        if(!$applicationName) {
            throw new Lucinda\MVC\STDOUT\XMLException("Attribute 'path' is mandatory for 'syslog' tag");
        }
        
        $pattern= (string) $xml["format"];
        if(!$pattern) {
            throw new Lucinda\MVC\STDOUT\XMLException("Attribute 'format' is mandatory for 'syslog' tag");
        }
        
        $this->logger = new Lucinda\Logging\SysLogger($applicationName, new Lucinda\Logging\LogFormatter($pattern));
    }
}