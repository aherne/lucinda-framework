<?php
use Lucinda\Logging\Logger;
use Lucinda\Logging\Driver\SysLog\Logger as SysLoggerDriver;

/**
 * Encapsulates logging to syslog based on XML settings
 */
class SyslogLogger extends \Lucinda\Logging\AbstractLoggerWrapper
{
    /**
     * {@inheritDoc}
     * @see \Lucinda\Logging\AbstractLoggerWrapper::setLogger()
     */
    protected function setLogger(SimpleXMLElement $xml): Logger
    {
        $applicationName = (string) $xml["application"];
        if (!$applicationName) {
            throw new Lucinda\STDOUT\XMLException("Attribute 'path' is mandatory for 'syslog' tag");
        }
        
        $pattern= (string) $xml["format"];
        if (!$pattern) {
            throw new Lucinda\STDOUT\XMLException("Attribute 'format' is mandatory for 'syslog' tag");
        }
        
        return new SysLoggerDriver($applicationName, new Lucinda\Logging\LogFormatter($pattern));
    }
}
