<?php

namespace Lucinda\Project\STDERR;

use Lucinda\Logging\Logger;
use Lucinda\Framework\AbstractReporter;
use Lucinda\Logging\RequestInformation;
use Lucinda\Logging\Wrapper;
use Lucinda\STDERR\ErrorType;

final class ErrorReporter extends AbstractReporter
{
    private Logger $logger;
    private Logger $fatalLogger;

    public function __construct(array $requestParameters, string $fullXMLPath, string $fullFatalXMLPath)
    {
        try {
            $this->logger = (new Wrapper(
                $this->getXML($fullXMLPath),
                $this->getRequestInformation($requestParameters)
            ))->getLogger();
            $this->fatalLogger = (new Wrapper(
                $this->getXML($fullFatalXMLPath),
                $this->getRequestInformation($requestParameters)
            ))->getLogger();
        } catch (\Throwable $throwable) {
            die("Logging could not be initialized: ".$throwable->getMessage());
        }
    }

    private function getXML(string $xmlPath): \SimpleXMLElement
    {
        $fullXMLPath = $xmlPath. ".xml";

        if (!file_exists($fullXMLPath)) {
            die("XML to setup error reporting is mandatory: " . $xmlPath);
        }

        $xml = simplexml_load_file($fullXMLPath);
        if ($xml === false) {
            die("Invalid error reporting XML: " . $xmlPath);
        }
        return $xml;
    }

    protected function isLoggable(ErrorType $errorType): bool
    {
        return true; // you can set here different rules based on ENVIRONMENT
    }

    protected function getLogger(ErrorType $errorType): Logger
    {
        return $errorType === ErrorType::INTERNAL 
        ? $this->fatalLogger
        : $this->logger;
    }

    private function getRequestInformation(array $requestParameters): RequestInformation
    {
        $requestInformation = new RequestInformation();
        $requestInformation->setUri($requestParameters["REQUEST_URI"]??"?");
        $requestInformation->setIpAddress($requestParameters["REMOTE_ADDR"]??"?");
        $requestInformation->setUserAgent($requestParameters["HTTP_USER_AGENT"]??"?");
        return $requestInformation;
    }
}