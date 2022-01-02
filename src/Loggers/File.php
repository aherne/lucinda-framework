<?php
namespace Lucinda\Project\Loggers;

use Lucinda\Logging\Logger;
use Lucinda\Logging\Driver\File\Logger as FileLoggerDriver;
use Lucinda\Logging\AbstractLoggerWrapper;
use Lucinda\MVC\ConfigurationException;
use Lucinda\Logging\LogFormatter;

/**
 * Encapsulates logging to files based on XML settings
 */
class File extends AbstractLoggerWrapper
{
    /**
     * {@inheritDoc}
     * @see \Lucinda\Logging\AbstractLoggerWrapper::setLogger()
     */
    protected function setLogger(\SimpleXMLElement $xml): Logger
    {
        $filePath = (string) $xml["path"];
        if (!$filePath) {
            throw new ConfigurationException("Attribute 'path' is mandatory for 'file' tag");
        }

        $pattern= (string) $xml["format"];
        if (!$pattern) {
            throw new ConfigurationException("Attribute 'format' is mandatory for 'file' tag");
        }

        return new FileLoggerDriver($filePath, new LogFormatter($pattern), (string) $xml["rotation"]);
    }
}
