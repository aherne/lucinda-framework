<?php
/**
 * Handler that prevents FrontController handling itself
 */
class EmergencyHandler implements Lucinda\MVC\STDERR\ErrorHandler
{
    public function handle($exception) {
        die($exception->getMessage());
    }
}

