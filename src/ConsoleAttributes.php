<?php

namespace Lucinda\Project;

use Lucinda\Logging\Logger;

/**
 * Implements \Lucinda\ConsoleSTDOUT\Attributes set by Lucinda Framework 3.0 event listeners. Developers can add more!
 */
class ConsoleAttributes extends \Lucinda\ConsoleSTDOUT\Attributes
{
    private ?Logger $logger = null;

    /**
     * Sets pointer to log messages with
     *
     * @param Logger $logger
     */
    public function setLogger(Logger $logger): void
    {
        $this->logger = $logger;
    }

    /**
     * Gets pointer to log messages with
     *
     * @return Logger|NULL
     */
    public function getLogger(): ?Logger
    {
        return $this->logger;
    }
}
