<?php
/**
 * Handler that prevents default error handler handling its own errors
 */
class EmergencyHandler implements Lucinda\MVC\STDERR\ErrorHandler
{
    public function handle($exception) {
        die($exception->getMessage());
    }
}

