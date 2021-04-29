<?php
use Lucinda\Logging\Driver\SysLog\Logger as SysLogger;
use Lucinda\Logging\Logger;

/**
 * Logs throwable into a dedicated SYSLOG server, whose details may vary according to development environment.
 */
class SyslogReporter extends \Lucinda\Framework\AbstractReporter
{
    /**
     * {@inheritDoc}
     * @see \Lucinda\Framework\AbstractReporter::getLogger()
     */
    public function getLogger(): Logger
    {
        $applicationName = (string) $this->xml["application"];
        if (!$applicationName) {
            throw new Lucinda\MVC\ConfigurationException("Attribute 'path' is mandatory for 'syslog' tag");
        }
        
        $pattern= (string) $this->xml["format"];
        if (!$pattern) {
            throw new Lucinda\MVC\ConfigurationException("Attribute 'format' is mandatory for 'syslog' tag");
        }
        
        return new SysLogger($applicationName, new Lucinda\Logging\LogFormatter($pattern));
    }
}
