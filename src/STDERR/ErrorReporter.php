<?php

namespace Lucinda\Project\STDERR;

use Lucinda\Logging\Logger;
use Lucinda\Framework\AbstractReporter;
use Lucinda\Logging\RequestInformation;
use Lucinda\Logging\Wrapper;
use Lucinda\STDERR\ErrorType;

/**
 * Routes reported failures to configured application and fatal loggers
 *
 * Construction loads both logger configurations and captures request metadata.
 * Initialization failure terminates with a minimal message because normal error
 * reporting cannot be relied upon at that point. INTERNAL failures use the
 * fatal logger; other classifications use the application logger.
 */
final class ErrorReporter extends AbstractReporter
{
    /**
     * Logger for ordinary reported failures
     */
    private Logger $logger;
    /**
     * Fallback logger for INTERNAL failures in the normal reporting flow
     */
    private Logger $fatalLogger;

    /**
     * Loads both logger configurations for the current request
     *
     * @param array<string, mixed> $requestParameters Server metadata such as
     * REQUEST_URI and REMOTE_ADDR
     * @param string $fullXMLPath Ordinary logger XML path without extension
     * @param string $fullFatalXMLPath Fatal logger XML path without extension
     */
    public function __construct(
        array $requestParameters,
        string $fullXMLPath,
        string $fullFatalXMLPath
    ) {
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
            die("Logging could not be initialized: "
                . $throwable->getMessage());
        }
    }

    /**
     * Loads required logger XML or terminates when it cannot be loaded
     *
     * @param string $xmlPath Absolute configuration path without extension
     * @return \SimpleXMLElement Parsed logger configuration
     */
    private function getXML(string $xmlPath): \SimpleXMLElement
    {
        $fullXMLPath = $xmlPath . ".xml";

        if (!file_exists($fullXMLPath)) {
            die("XML to setup error reporting is mandatory: " . $xmlPath);
        }

        $xml = simplexml_load_file($fullXMLPath);
        if ($xml === false) {
            die("Invalid error reporting XML: " . $xmlPath);
        }
        return $xml;
    }

    /**
     * Accepts every error classification for the configured logging policy
     *
     * @param ErrorType $errorType Classification selected by STDERR
     * @return bool True because the default policy logs every classification
     */
    protected function isLoggable(ErrorType $errorType): bool
    {
        return true; // you can set here different rules based on ENVIRONMENT
    }

    /**
     * Selects the fatal logger only for an internal reporting failure
     *
     * @param ErrorType $errorType Classification of the reported exception
     * @return Logger Logger configured for this error classification
     */
    protected function getLogger(ErrorType $errorType): Logger
    {
        return $errorType === ErrorType::INTERNAL
        ? $this->fatalLogger
        : $this->logger;
    }

    /**
     * Normalizes available server metadata for log entries
     *
     * @param array<string, mixed> $requestParameters Current server metadata
     * @return RequestInformation URI, client IP and user agent with fallback
     * values
     */
    private function getRequestInformation(
        array $requestParameters
    ): RequestInformation {
        $requestInformation = new RequestInformation();
        $requestInformation->setUri($requestParameters["REQUEST_URI"] ?? "?");
        $requestInformation->setIpAddress(
            $requestParameters["REMOTE_ADDR"] ?? "?"
        );
        $requestInformation->setUserAgent(
            $requestParameters["HTTP_USER_AGENT"] ?? "?"
        );
        return $requestInformation;
    }
}
