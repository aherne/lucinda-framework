<?php
use Lucinda\Logging\Logger;
use Lucinda\Logging\Driver\File\Logger as FileLoggerDriver;

/**
 * Encapsulates logging to files based on XML settings
 */
class FileLogger extends \Lucinda\Logging\AbstractLoggerWrapper
{
    /**
     * {@inheritDoc}
     * @see \Lucinda\Logging\AbstractLoggerWrapper::setLogger()
     */
    protected function setLogger(SimpleXMLElement $xml): Logger
    {
        $filePath = (string) $xml["path"];
        if (!$filePath) {
            throw new Lucinda\MVC\ConfigurationException("Attribute 'path' is mandatory for 'file' tag");
        }
        
        $pattern= (string) $xml["format"];
        if (!$pattern) {
            throw new Lucinda\MVC\ConfigurationException("Attribute 'format' is mandatory for 'file' tag");
        }
        
        return new FileLoggerDriver($filePath, (string) $xml["rotation"], new Lucinda\Logging\LogFormatter($pattern));
    }
}
