<?php
require_once("libraries/php-errors-api/loader.php");
require_once("application/models/errors/reporters/LogReporter.php");
require_once("application/models/ComponentFinder.php");

/**
 * Sets up customized error reporting by connecting PHP-ERRORS-API and PHP-LOGGING-API with CONFIGURATION.XML @ SERVLETS API,
 * after EnvironmentListener has ran.  * Reads XML "errors" tag for sub-tags that contain error policies, per detected environment:
 * - reporters: (OPTIONAL) this tag holds one or more components to delegate saving errors to. If none supplied, no reporting is done.
 * - renderer: (OPTIONAL) this tag holds component to delegate display when an error was encountered. If none supplied, no rendering is done.
 *
 * Syntax for XML "security" tag is:
 * <errors>
 * 		<handlers>
 * 			<{ENVIRONMENT_NAME}>
 * 				<reporters>...</reporters>
 * 				<renderer>...</renderer>
 * 			</{ENVIRONMENT_NAME}>
 * 		<handlers>
 * </errors>
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
	 * Loads and returns error reporters based on contents of errors.handlers.{environment}.reporters XML tag. Tag syntax:
	 *
	 * <reporters>
	 * 		<file path="{FILE_PATH}" rotation="{ROTATION_PATTERN}"/>
	 * 		<syslog application="{APPLICATION_NAME}"/>
	 * 		<sql table="{TABLE_NAME}" server="{SERVER_NAME}" rotation="{ROTATION_PATTERN}"/>
	 * 		<reporter class="{CLASS}" .../>
	 * </reporters>
	 *
	 * Of which:
	 * - "file": reporting is done in a file on your server's disk (the DEFAULT). Corresponds to tag:
	 * 		<file path="{FILE_PATH}" rotation="{ROTATION_PATTERN}"/>
	 * - "syslog": reporting is done via syslog service running on your server. Corresponds to tag:
	 * 		<syslog application="{APPLICATION_NAME}"/>
	 * - "sql": reporting is done into an sql table. Corresponds to tag:
	 * 		<sql table="{TABLE_NAME}" server="{SERVER_NAME}" rotation="{ROTATION_PATTERN}"/>
	 * - "reporter": if you want to add a custom reporter (class must extend CustomReporter class)
	 * 		<reporter class="{CLASS}" .../>
	 *
	 * It is allowed to have multiple reporters at the same time! If no reporter is defined, bug is not reported at all.
	 *
	 * @throws ApplicationException
	 * @return ErrorReporter[] List of objects to delegate error reporting to.
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
	 * Loads and returns error renderer based on contents of errors.handlers.{environment}.renderer XML tag and page format (extension). Tag syntax:
	 *
	 * <renderer display_errors="{0 OR 1}">
	 * 		<{extension} ?(class="{CLASS}" ...)/>
	 * </renderer>
	 *
	 * When request doesn't match an extension above, no error rendering is done (a blank page is outputted). Otherwise:
	 * - IF no "class" parameter is supplied, rendering is handled by framework as long as extension is html or json. For other extensions, a blank page is outputted.
	 * - ELSE, a file by same name as class is searched and required in "application/models" folder, then instance of that class (which must extend CustomRenderer)
	 * will be used in rendering (this is for customized rendering)
	 *
	 * Tag must contain a single renderer for one format, or none (in which case default renderer will be used)
	 *
	 * @throws ApplicationException If XML structure is invalid
	 * @return ErrorRenderer Object to delegate error rendering to.
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
		switch($extension) {
			case "html":
				if(empty($renderer->$extension["class"])) {
					require_once("application/models/errors/renderers/HtmlRenderer.php");
					return new HtmlRenderer($showErrors, $this->application->getDefaultCharacterEncoding());
				}
				break;
			case "json":
				if(empty($renderer->$extension["class"])) {
					require_once("application/models/errors/renderers/JsonRenderer.php");
					return new JsonRenderer($showErrors, $this->application->getDefaultCharacterEncoding());
				}
			default:
				return; // by default, no renderer
		}

		// use custom renderer
		require_once("application/models/errors/renderers/CustomRenderer.php");
		$componentFinder = new ComponentFinder($renderer->$extension, "CustomRenderer", "errors.handlers.{environment}.renderer.{extension}");
		return $componentFinder->getComponent();
	}
}