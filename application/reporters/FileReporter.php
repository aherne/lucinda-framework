<?php
use Lucinda\Logging\Driver\File\Logger as FileLogger;
use Lucinda\Logging\Logger;

/**
 * Logs throwable into file, whose details may vary according to development environment.
 */
class FileReporter extends \Lucinda\Framework\AbstractReporter
{
    /**
     * {@inheritDoc}
     * @see \Lucinda\Framework\AbstractReporter::getLogger()
     */
    public function getLogger(): Logger
    {
        $rootFolder = dirname(dirname(__DIR__));
        $filePath = $rootFolder."/".$this->xml["path"];
        if (!$filePath) {
            throw new Lucinda\MVC\ConfigurationException("Attribute 'path' is mandatory for 'file' tag");
        }
        
        $pattern= (string) $this->xml["format"];
        if (!$pattern) {
            throw new Lucinda\MVC\ConfigurationException("Attribute 'format' is mandatory for 'file' tag");
        }
        
        return new FileLogger($filePath, (string) $this->xml["rotation"], new Lucinda\Logging\LogFormatter($pattern));
    }
}
