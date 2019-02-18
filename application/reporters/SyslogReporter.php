<?php
require_once(dirname(dirname(__DIR__))."/vendor/lucinda/framework-engine/src/error_reporting/LogReporter.php");

/**
 * Logs error into a dedicated SYSLOG server, whose details may vary according to development environment.
 */
class SyslogReporter extends \Lucinda\Framework\LogReporter {
    /**
     * {@inheritDoc}
     * @see \Lucinda\Framework\LogReporter::getLogger()
     */
    protected function getLogger(SimpleXMLElement $xml) {
        require_once(dirname(dirname(__DIR__))."/vendor/lucinda/logging/src/SysLogger.php");
        
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