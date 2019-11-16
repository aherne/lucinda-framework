<?php
/**
 * Binds STDERR MVC with Logging API in order to reporter errors to loggers in same content type as current page
 */
class ErrorListener extends \Lucinda\MVC\STDOUT\RequestListener
{
    /**
     * {@inheritDoc}
     * @see \Lucinda\MVC\STDOUT\Runnable::run()
     */
    public function run()
    {
        $handler = \Lucinda\MVC\STDERR\PHPException::getErrorHandler();
        if ($handler instanceof \Lucinda\MVC\STDERR\FrontController) {
            $handler->setDisplayFormat($this->request->getValidator()->getFormat());
        }
    }
}
