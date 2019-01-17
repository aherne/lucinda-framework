<?php
/**
 * Logs message into file on disk, whose location varies according to development environment.  
 * ATTENTION: web server must have write access on folder file is located into! 
 */
class FileLoggerWrapper extends \Lucinda\Framework\AbstractLoggerWrapper {
    /**
     * {@inheritDoc}
     * @see \Lucinda\Framework\AbstractLoggerWrapper::setLogger()
     */
    protected function setLogger(SimpleXMLElement $xml) {
        require_once("vendor/lucinda/logging/src/FileLogger.php");
        
        $filePath = (string) $xml["path"];
        if(!$filePath) {
            throw new Lucinda\MVC\STDOUT\XMLException("Attribute 'path' is mandatory for 'file' tag");
        }
        
        $pattern= (string) $xml["format"];
        if(!$pattern) {
            throw new Lucinda\MVC\STDOUT\XMLException("Attribute 'format' is mandatory for 'file' tag");
        }
        
        return new Lucinda\Logging\FileLogger($filePath, (string) $xml["rotation"], new Lucinda\Logging\LogFormatter($pattern));
    }
}