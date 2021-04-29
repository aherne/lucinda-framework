<?php
/**
 * Sets up STDERR MVC API to use response format of route requested
 */
class ErrorListener extends \Lucinda\STDOUT\EventListeners\Request
{
    /**
     * {@inheritDoc}
     * @see \Lucinda\MVC\Runnable::run()
     */
    public function run(): void
    {
        $handler = \Lucinda\STDERR\PHPException::getErrorHandler();
        if ($handler instanceof \Lucinda\STDERR\FrontController) {
            $handler->setDisplayFormat($this->attributes->getValidFormat());
        }
    }
}
