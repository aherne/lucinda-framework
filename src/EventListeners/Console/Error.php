<?php

namespace Lucinda\Project\EventListeners\Console;

use Lucinda\MVC\EventListener\UnFaceted;
use Lucinda\STDERR\PHPException;
use Lucinda\STDERR\FrontController;

/**
 * Sets up STDERR MVC API to use response format of route requested
 */
class Error implements UnFaceted
{

    public function run(): void
    {
        $handler = PHPException::getErrorHandler();
        if ($handler instanceof FrontController) {
            $handler->setDisplayFormat("console");
        }
    }
}
