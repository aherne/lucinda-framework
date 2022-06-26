<?php

namespace Lucinda\Project\Loggers;

use Lucinda\Logging\Logger;
use Lucinda\Logging\Driver\SysLog\Logger as SysLoggerDriver;
use Lucinda\Logging\AbstractLoggerWrapper;
use Lucinda\MVC\ConfigurationException;
use Lucinda\Logging\LogFormatter;

/**
 * Encapsulates logging to syslog based on XML settings
 */
class SysLog extends AbstractLoggerWrapper
{
    /**
     * {@inheritDoc}
     *
     * @see \Lucinda\Logging\AbstractLoggerWrapper::setLogger()
     */
    protected function setLogger(\SimpleXMLElement $xml): Logger
    {
        $applicationName = (string) $xml["application"];
        if (!$applicationName) {
            throw new ConfigurationException("Attribute 'path' is mandatory for 'syslog' tag");
        }

        $pattern= (string) $xml["format"];
        if (!$pattern) {
            throw new ConfigurationException("Attribute 'format' is mandatory for 'syslog' tag");
        }

        return new SysLoggerDriver(
            $applicationName,
            new LogFormatter($pattern, $this->requestInformation)
        );
    }
}
