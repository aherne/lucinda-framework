<?php

namespace Lucinda\Project\EventListeners;

use Lucinda\MVC\EventListener\UnFaceted;
use Lucinda\STDERR\PHPException;
use Lucinda\STDERR\FrontController;
use Lucinda\STDOUT\Validators\ValidatedRequest;

/**
 * Sets up STDERR MVC API to use response format of route requested
 */
class Error implements UnFaceted
{
    private ValidatedRequest $validatedRequest;

    public function __construct(ValidatedRequest $validatedRequest)
    {
        $this->validatedRequest = $validatedRequest;
    }

    public function run(): void
    {
        $handler = PHPException::getErrorHandler();
        if ($handler instanceof FrontController) {
            $handler->setDisplayFormat($this->validatedRequest->getFormat());
        }
    }
}
