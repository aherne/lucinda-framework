<?php

namespace Lucinda\Project\Facets;

use Lucinda\Logging\Logger;
use Lucinda\MVC\Facet;

final class Loggers implements Facet
{
    private ?Logger $logger = null;

    public function setLogger(Logger $logger): void
    {
        $this->logger = $logger;
    }

    public function getLogger(): ?Logger
    {
        return $this->logger;
    }
}