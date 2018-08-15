<?php
require_once("vendor/lucinda/logging/loader.php");
require_once("vendor/lucinda/framework-engine/src/errors/LogReporter.php");

/**
 * Fine tunes error handling in your application by binding PHP-ERRORS-API & PHP-LOGGING-API with content of "errors" tag @ CONFIGURATION.XML,
 * itself handled by SERVLETS API.
 *
 * Syntax for "errors" XML  tag is:
 * <errors>
 * 			<{ENVIRONMENT_NAME}>
 * 				<reporters>...</reporters>
 * 				<renderer>...</renderer>
 * 			</{ENVIRONMENT_NAME}>
 * </errors>
 *
 * Where:
 * - reporters: (optional) holds one or more components to delegate saving errors to. If none supplied, no reporting is done.
 * - renderer: (optional) holds component to delegate display when an error was encountered. If none supplied, no rendering is done.
 *
 * Because behavior depends on environment, this listener requires EnvironmentDetector to be ran beforehand. After automated error handling is injected,
 * its instance will be made available across application as "error_handler" application attribute.
 *
 * @attribute error_handler
 */
class ErrorListener extends RequestListener {
    /**
     * {@inheritDoc}
     * @see Runnable::run()
     */
    public function run() {
        $handler = \Lucinda\MVC\STDERR\PHPException::getErrorHandler();
        $handler->setContentType($this->request->getValidator()->getContentType());
    }
}