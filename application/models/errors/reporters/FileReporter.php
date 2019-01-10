<?php
require_once(dirname(__DIR__,4)."/vendor/lucinda/framework-engine/src/error_reporting/LogReporter.php");

/**
 * Logs error into file on disk, whose location varies according to development environment. 
 * ATTENTION: web server must have write access on folder file is located into! 
 */
class FileReporter extends Lucinda\Framework\LogReporter {
    /**
     * {@inheritDoc}
     * @see Lucinda\Framework\LogReporter::getLogger()
     */
    protected function getLogger(SimpleXMLElement $xml) {
        $rootFolder = dirname(dirname(dirname(dirname(__DIR__))));
        require_once($rootFolder."/vendor/lucinda/logging/src/FileLogger.php");
        
        $filePath = $rootFolder."/".$xml["path"];
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