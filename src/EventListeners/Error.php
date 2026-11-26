<?php

namespace Lucinda\Project\EventListeners;

use Lucinda\MVC\EventListener\UnFaceted;
use Lucinda\STDERR\PHPException;
use Lucinda\STDERR\FrontController;
use Lucinda\STDOUT\Validators\ValidatedRequest;

/**
 * Aligns active STDERR rendering with the validated HTTP request format
 *
 * The listener retains the validated request until run(). It updates only an
 * active STDERR front controller and otherwise leaves processing unchanged.
 */
final class Error implements UnFaceted
{
    /**
     * Validated route and requested response format
     */
    private ValidatedRequest $validatedRequest;

    /**
     * Captures the validated route used to select the error response format
     *
     * @param ValidatedRequest $validatedRequest Current route and response
     * format
     */
    public function __construct(ValidatedRequest $validatedRequest)
    {
        $this->validatedRequest = $validatedRequest;
    }

    /**
     * Updates an active STDERR front controller to match the route format
     *
     * @return void
     */
    public function run(): void
    {
        $handler = PHPException::getErrorHandler();
        if ($handler instanceof FrontController) {
            $handler->setDisplayFormat($this->validatedRequest->getFormat());
        }
    }
}
