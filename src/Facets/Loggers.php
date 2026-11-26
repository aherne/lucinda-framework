<?php

namespace Lucinda\Project\Facets;

use Lucinda\Logging\Logger;
use Lucinda\MVC\Facet;

/**
 * Publishes the request logger for constructor injection
 *
 * The facet starts empty. The logging listener assigns the initialized logger,
 * which remains shared with all consumers of this facet.
 */
final class Loggers implements Facet
{
    /**
     * Shared request logger, or null before logging initialization
     */
    private ?Logger $logger = null;

    /**
     * Sets the initialized logger shared by request consumers
     *
     * @param Logger $logger Logger configured by the logging listener
     * @return void
     */
    public function setLogger(Logger $logger): void
    {
        $this->logger = $logger;
    }

    /**
     * Gets the logger published during request initialization
     *
     * @return Logger|null Shared logger, or null before initialization
     */
    public function getLogger(): ?Logger
    {
        return $this->logger;
    }
}
