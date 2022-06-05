<?php

namespace Lucinda\Project\ErrorReporters;

use Lucinda\Framework\LoggingRequestInformation;
use Lucinda\Logging\Driver\SysLog\Logger as SysLogger;
use Lucinda\Logging\Logger;
use Lucinda\Framework\AbstractReporter;
use Lucinda\MVC\ConfigurationException;
use Lucinda\Logging\LogFormatter;

/**
 * Logs throwable into a dedicated SYSLOG server, whose details may vary according to development environment.
 */
class SysLog extends AbstractReporter
{
    /**
     * {@inheritDoc}
     *
     * @see \Lucinda\Framework\AbstractReporter::getLogger()
     */
    public function getLogger(): Logger
    {
        $applicationName = (string) $this->xml["application"];
        if (!$applicationName) {
            throw new ConfigurationException("Attribute 'path' is mandatory for 'syslog' tag");
        }

        $pattern= (string) $this->xml["format"];
        if (!$pattern) {
            throw new ConfigurationException("Attribute 'format' is mandatory for 'syslog' tag");
        }

        $loggingRequestInfo = new LoggingRequestInformation();
        return new SysLogger(
            $applicationName,
            new LogFormatter($pattern, $loggingRequestInfo->getRequestInformation())
        );
    }
}
