<?php
require_once("libraries/php-errors-api/loader.php");
require_once("libraries/php-logging-api/loader.php");
require_once("application/models/ErrorRendererFinder.php");
require_once("application/models/ErrorReportersFinder.php");

/**
 * Sets up error handling in your application by binding PHP-ERRORS-API & PHP-LOGGING-API with content of "errors" tag @ CONFIGURATION.XML, 
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
class ErrorListener extends ApplicationListener {
	const DEFAULT_LOG_FILE = "errors";

	/**
	 * {@inheritDoc}
	 * @see Runnable::run()
	 */
	public function run() {
		// generate error handler
		$errorHandler = new ErrorHandler();
		$reporters = $this->getReporters();
		foreach($reporters as $reporter) {
			$errorHandler->addReporter($reporter);
		}
		$renderer = $this->getRenderer();
		if($renderer) {
			$errorHandler->setRenderer($this->getRenderer());
		}
		
		// inject handler into classes that automatically catch PHP errors
		PHPException::setErrorHandler($errorHandler);
		set_exception_handler(array($errorHandler,"handle"));
		ini_set("display_errors",0);

		// saves error_handler for latter manipulation (if any)
		$this->application->setAttribute("error_handler", $errorHandler);
	}

	/**
	 * Finds error reporter among children of errors.{ENVIRONMENT}.reporters tag. Following children are recognized:
	 * 		<file path="{FILE_PATH}" rotation="{ROTATION_PATTERN}"/>
	 * 		<syslog application="{APPLICATION_NAME}"/>
	 * 		<sql table="{TABLE_NAME}" server="{SERVER_NAME}" rotation="{ROTATION_PATTERN}"/>
	 * 		<logger class="{CLASS}" .../>
	 *
	 * Of which:
	 * - "file": reporting is done in a file on your server's disk
	 * - "syslog": reporting is done via syslog service running on your server
	 * - "sql": reporting is done into an sql table
	 * - "logger": if you want to add a custom reporter (class must extend CustomLogger)
	 *
	 * It is allowed to have multiple reporters at the same time! If no reporter is defined, error will not be reported at all.
	 *
	 * @throws ApplicationException
	 * @return LogReporter[] List of ErrorReporter to delegate error reporting to.
	 */
	private function getReporters() {
		$erf = new ErrorReportersFinder($this->application->getXML()->errors, $this->application->getAttribute("environment"));
		return $erf->getReporters();
	}


	/**
	 * Finds child of  errors.{ENVIRONMENT}.renderer XML tag that matches page format (extension). Tag syntax:
	 *
	 * <renderer display_errors="{0 OR 1}">
	 * 		<{extension} ?(class="{CLASS}" ...)/>
	 * 		...
	 * </renderer>
	 *
	 * Using "display_errors" flag, one toggles showing details about error on screen. Setting it to "1" is recommended for development environments,
	 * but it should always be "0" on live environments (otherwise details of your application will be leaked to outside world).
	 *
	 * If no tag is found to match extension, no error rendering is done (a blank page is outputted). Otherwise:
	 * - IF no "class" parameter is supplied, rendering is handled by framework as long as extension is html or json. For other extensions, a blank page is outputted.
	 * - ELSE, a file by same name as class is searched and required in "application/models" folder, then an instance of that class (which must extend CustomRenderer)
	 * is returned.
	 *
	 * @throws ApplicationException If XML structure is invalid
	 * @return ErrorRenderer|null Object to delegate error rendering to.
	 */
	private function getRenderer() {
		$erf = new ErrorRendererFinder($this->application->getXML()->errors, $this->application);
		return $erf->getRenderer();
	}
}