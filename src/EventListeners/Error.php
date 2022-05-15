<?php

namespace Lucinda\Project\EventListeners;

use Lucinda\STDERR\PHPException;
use Lucinda\STDERR\FrontController;
use Lucinda\STDOUT\EventListeners\Request;

/**
 * Sets up STDERR MVC API to use response format of route requested
 */
class Error extends Request
{
    /**
     * {@inheritDoc}
     * @see \Lucinda\MVC\Runnable::run()
     */
    public function run(): void
    {
        $handler = PHPException::getErrorHandler();
        if ($handler instanceof FrontController) {
            $handler->setDisplayFormat($this->attributes->getValidFormat());
        }
    }
}
