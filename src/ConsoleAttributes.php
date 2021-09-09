<?php
namespace Lucinda\Project;

/**
 * Implements \Lucinda\ConsoleSTDOUT\Attributes set by Lucinda Framework 3.0 event listeners. Developers can add more!
 */
class ConsoleAttributes extends \Lucinda\ConsoleSTDOUT\Attributes
{
    private $logger;
    
    /**
     * Sets pointer to log messages with
     *
     * @param \Lucinda\Logging\Logger $logger
     */
    public function setLogger(\Lucinda\Logging\Logger $logger): void
    {
        $this->logger = $logger;
    }
    
    /**
     * Gets pointer to log messages with
     *
     * @return \Lucinda\Logging\Logger|NULL
     */
    public function getLogger(): ?\Lucinda\Logging\Logger
    {
        return $this->logger;
    }
}