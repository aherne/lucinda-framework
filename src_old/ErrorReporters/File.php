<?php

namespace Lucinda\Project\ErrorReporters;

use Lucinda\Framework\LoggingRequestInformation;
use Lucinda\Logging\Driver\File\Logger as FileLogger;
use Lucinda\Logging\Logger;
use Lucinda\Framework\AbstractReporter;
use Lucinda\MVC\ConfigurationException;
use Lucinda\Logging\LogFormatter;

/**
 * Logs throwable into file, whose details may vary according to development environment.
 */
class File extends AbstractReporter
{
    /**
     * {@inheritDoc}
     *
     * @see \Lucinda\Framework\AbstractReporter::getLogger()
     */
    public function getLogger(): Logger
    {
        $rootFolder = dirname(__DIR__, 2);

        $filePath =  (string) $this->xml["path"];
        if (!$filePath) {
            throw new ConfigurationException("Attribute 'path' is mandatory for 'file' tag");
        }

        $pattern= (string) $this->xml["format"];
        if (!$pattern) {
            throw new ConfigurationException("Attribute 'format' is mandatory for 'file' tag");
        }

        $loggingRequestInfo = new LoggingRequestInformation();
        return new FileLogger(
            $rootFolder."/".$filePath,
            new LogFormatter($pattern, $loggingRequestInfo->getRequestInformation()),
            (string) $this->xml["rotation"]
        );
    }
}
