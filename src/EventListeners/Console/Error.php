<?php

namespace Lucinda\Project\EventListeners\Console;

use Lucinda\STDERR\PHPException;
use Lucinda\STDERR\FrontController;
use Lucinda\ConsoleSTDOUT\EventListeners\Application;

/**
 * Sets up STDERR MVC API to use response format of route requested
 */
class Error extends Application
{
    /**
     * {@inheritDoc}
     *
     * @see \Lucinda\MVC\Runnable::run()
     */
    public function run(): void
    {
        $handler = PHPException::getErrorHandler();
        if ($handler instanceof FrontController) {
            $handler->setDisplayFormat("console");
        }
    }
}
