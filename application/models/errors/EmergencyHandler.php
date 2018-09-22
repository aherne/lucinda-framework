<?php
/**
 * Error handler that prevents STDERR MVC FrontController handling its own errors. Developers may need to modify contents of handle method to give more or
 * less information about bug encountered.
 */
class EmergencyHandler implements Lucinda\MVC\STDERR\ErrorHandler
{
    /**
     * {@inheritDoc}
     * @see \Lucinda\MVC\STDERR\ErrorHandler::handle()
     */
    public function handle($exception) {
        die($exception->getMessage());
    }
}

