<?php
require_once("libraries/php-errors-api/loader.php");
require_once("application/models/errors/reporters/LogReporter.php");
require_once("application/models/ComponentFinder.php");

/**
 * Sets up error handling in your application by binding PHP-ERRORS-API & PHP-LOGGING-API with content of "errors" tag @ CONFIGURATION.XML, 
 * itself handled by SERVLETS API.
 * 
 * Syntax for "errors" XML  tag is:
 * <errors>
 * 		<handlers>
 * 			<{ENVIRONMENT_NAME}>
 * 				<reporters>...</reporters>
 * 				<renderer>...</renderer>
 * 			</{ENVIRONMENT_NAME}>
 * 		<handlers>
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
		$errorHandler = new ErrorHandler();
		$reporters = $this->getReporters();
		foreach($reporters as $reporter) {
			$errorHandler->addReporter($reporter);
		}
		$renderer = $this->getRenderer();
		if($renderer) {
			$errorHandler->setRenderer($this->getRenderer());
		}
		PHPException::setErrorHandler($errorHandler);
		set_exception_handler(array($errorHandler,"handle"));
		ini_set("display_errors",0);

		// saves error_handler for latter manipulation (if any)
		$this->application->setAttribute("error_handler", $errorHandler);
	}

	/**
	 * Finds error reporter among children of errors.handlers.{ENVIRONMENT}.reporters tag. Following children are recognized:
	 * 		<file path="{FILE_PATH}" rotation="{ROTATION_PATTERN}"/>
	 * 		<syslog application="{APPLICATION_NAME}"/>
	 * 		<sql table="{TABLE_NAME}" server="{SERVER_NAME}" rotation="{ROTATION_PATTERN}"/>
	 * 		<reporter class="{CLASS}" .../>
	 *
	 * Of which:
	 * - "file": reporting is done in a file on your server's disk
	 * - "syslog": reporting is done via syslog service running on your server
	 * - "sql": reporting is done into an sql table
	 * - "reporter": if you want to add a custom reporter (class must extend CustomReporter class)
	 *
	 * It is allowed to have multiple reporters at the same time! If no reporter is defined, error will not be reported at all.
	 *
	 * @throws ApplicationException
	 * @return ErrorReporter[] List of ErrorReporter to delegate error reporting to.
	 */
	private function getReporters() {
		$output = array();

		// look for reporters tag
		$environment = $this->application->getAttribute("environment");
		$xml = $this->application->getXML()->errors;
		if(empty($xml) || empty($xml->handlers) || empty($xml->handlers->$environment) || empty($xml->handlers->$environment->reporters)) {
			return array();
		}
		$reporters = $xml->handlers->$environment->reporters;

		// append file reporting
		if($reporters->file) {
			require_once("libraries/php-logging-api/src/FileLogger.php");
				
			$filePath = (string) $reporters->file["path"];
			if(!$filePath) {
				throw new ApplicationException("Property 'path' missing in configuration.xml tag: errors.handlers.{environment}.reporters.file!");
			}
			$output[] = new LogReporter(new FileLogger($filePath, (string) $reporters->file["rotation"]));
		}

		// append syslog reporting
		if($reporters->syslog) {
			require_once("libraries/php-logging-api/src/SysLogger.php");
				
			$applicationName = (string) $reporters->syslog["application"];
			if(!$applicationName) {
				throw new ApplicationException("Property 'application' missing in configuration.xml tag: errors.handlers.{environment}.reporters.syslog!");
			}
			$output[] = new LogReporter(new SysLogger($applicationName));
		}

		// append sql reporting
		if($reporters->sql) {
			require_once("libraries/php-logging-api/src/SQLLogger.php");
				
			$serverName = (string) $reporters->sql["server"];
			if(!class_exists("SQLConnectionFactory")) {
				throw new ApplicationException("SQLDataSourceInjector listener has not ran!");
			}
			$tableName = (string) $reporters->sql["table"];
			if(!$tableName) {
				throw new ApplicationException("Property 'table' missing in configuration.xml tag: errors.handlers.{environment}.reporters.sql!");
			}
			$output[] = new LogReporter(new SQLLogger($tableName, ($serverName?SQLConnectionFactory::getInstance($serverName):SQLConnectionSingleton::getInstance()), (string) $reporters->sql["rotation"]));
		}

		// append custom reporter
		if($reporters->reporter) {
			require_once("application/models/errors/reporters/CustomReporter.php");
			$componentFinder = new ComponentFinder($reporters->reporter, "CustomReporter", "errors.handlers.{environment}.reporters.reporter");
			$output[] =$componentFinder->getComponent();
		}

		return $output;
	}


	/**
	 * Finds child of  errors.handlers.{ENVIRONMENT}.renderer XML tag that matches page format (extension). Tag syntax:
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
		// clears all headers already sent (if any), to guarantee a fresh rendering
		header_remove();

		// get extension
		$extension = $this->application->getDefaultExtension();
		$pathRequested = str_replace("?".$_SERVER["QUERY_STRING"],"",$_SERVER["REQUEST_URI"]);
		$dotPosition = strrpos($pathRequested,".");
		if($dotPosition!==false) {
			$extension = strtolower(substr($pathRequested,$dotPosition+1));
		}

		// render error
		$environment = $this->application->getAttribute("environment");
		$xml = $this->application->getXML()->errors;
		$renderer = (!empty($xml) && !empty($xml->handlers) && !empty($xml->handlers->$environment)?$xml->handlers->$environment->renderer:null);
		$showErrors = ($renderer!=NULL && ((string) $renderer["display_errors"])?true:false);
		if(!$renderer || !isset($renderer->$extension)) {
			return null; // it is allowed to render nothing
		}

		// perform rendering
		$renderer = $renderer->$extension;
		if(!isset($renderer["class"])) {
			// use default
			switch($extension) {
				case "html":
					require_once("application/models/errors/renderers/HtmlRenderer.php");
					return new HtmlRenderer($showErrors, $this->application->getDefaultCharacterEncoding());
					break;
				case "json":
					require_once("application/models/errors/renderers/JsonRenderer.php");
					return new JsonRenderer($showErrors, $this->application->getDefaultCharacterEncoding());
				default:
					return; // by default, no renderer
			}
		} else {
			// use custom renderer
			require_once("application/models/errors/renderers/CustomRenderer.php");
			$componentFinder = new ComponentFinder($renderer, "CustomRenderer", "errors.handlers.{environment}.renderer.{extension}");
			return $componentFinder->getComponent();
		}

	}
}