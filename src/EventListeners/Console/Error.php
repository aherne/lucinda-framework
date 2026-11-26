<?php

namespace Lucinda\Project\EventListeners\Console;

use Lucinda\MVC\EventListener\UnFaceted;
use Lucinda\STDERR\PHPException;
use Lucinda\STDERR\FrontController;

/**
 * Selects console rendering on the active STDERR front controller
 *
 * Console request setup invokes this listener before error output is needed.
 * A missing or different registered error handler is left unchanged.
 */
final class Error implements UnFaceted
{
    /**
     * Selects console formatting on the active STDERR front controller
     *
     * @return void
     */
    public function run(): void
    {
        $handler = PHPException::getErrorHandler();
        if ($handler instanceof FrontController) {
            $handler->setDisplayFormat("console");
        }
    }
}
