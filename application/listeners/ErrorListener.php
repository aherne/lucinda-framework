<?php
require_once("libraries/php-errors-api/loader.php");
require_once("application/models/errors/reporters/LogReporter.php");
require_once("application/models/ComponentFinder.php");

/**
 * Sets up customized error reporting by connecting PHP-ERRORS-API and PHP-LOGGING-API with CONFIGURATION.XML @ SERVLETS API, 
 * after EnvironmentListener has ran.  * Reads XML "errors" tag for sub-tags that contain error policies, per detected environment:
 * - reporting: (OPTIONAL) this tag holds one or more components to delegate saving errors to. If none supplied, default reporter is used.
 * - rendering: (OPTIONAL) this tag holds component to delegate display when an error was encountered. If none supplied, default renderer is used.
 * 
 * Syntax for XML "security" tag is:
 * <errors>
 * 		<{ENVIRONMENT_NAME}>
 * 			<reporting>...</reporting>
 * 			<rendering>...</rendering>
 * 		</{ENVIRONMENT_NAME}>
 * </errors>
 * 
 * NOTE: this listener is not needed if your expect application to work with default reporting & rendering.
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
		$errorHandler->setRenderer($this->getRenderer());
		PHPException::setErrorHandler($errorHandler);
		set_exception_handler(array($errorHandler,"handle"));
		ini_set("display_errors",0);
	}
	
	/**
	 * Loads and returns error reporters based on contents of errors.{ENVIRONMENT}.reporting XML tag. 
	 * 
	 * Lucinda Framework embeds three reporters:
	 * - file: reporting is done in a file on your server's disk (the DEFAULT)
	 * - syslog: reporting is done via syslog service running on your server
	 * - sql: reporting is done into an sql database
	 * but also allows you to add your own reporter using "reporter" tag!
	 * 
	 * Supported syntax for XML tag above is:
	 * <reporting>
	 * 		<file path="{FILE_PATH}" rotation="{ROTATION_PATTERN}"/>
	 * 		<syslog application="{APPLICATION_NAME}"/>
	 * 		<sql table="{TABLE_NAME}" server="{SERVER_NAME}" rotation="{ROTATION_PATTERN}"/>
	 * 		<reporter class="{CLASS}" folder="{FOLDER}" .../>
	 * </reporting>
	 * 
	 * Tag can contain one or more of above components, or none (in which case default reporter will be used)
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
				throw new ApplicationException("Property 'path' missing in configuration.xml tag: errors.{environment}.reporters.file!");
			}
			$output[] = new LogReporter(new FileLogger($filePath, (string) $reporters->file["rotation"]));
		}
		
		// append syslog reporting
		if($reporters->syslog) {
			require_once("libraries/php-logging-api/src/SysLogger.php");
			
			$applicationName = (string) $reporters->syslog["application"];
			if(!$applicationName) {
				throw new ApplicationException("Property 'application' missing in configuration.xml tag: errors.{environment}.reporters.syslog!");
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
				throw new ApplicationException("Property 'table' missing in configuration.xml tag: errors.{environment}.reporters.sql!");
			}	
			$output[] = new LogReporter(new SQLLogger($tableName, ($serverName?SQLConnectionFactory::getInstance($serverName):SQLConnectionSingleton::getInstance()), (string) $reporters->sql["rotation"]));
		}
		
		// append custom reporter
		if($reporters->reporter) {
			require_once("application/models/errors/reporters/CustomReporter.php");
			$componentFinder = new ComponentFinder($reporters->reporter, "CustomReporter", "errors.{environment}.reporters.reporter");
			$output[] =$componentFinder->getComponent();
		}
		
		return $output;
	}
	
	
	/**
	 * Loads and returns error renderer based on contents of errors.{ENVIRONMENT}.rendering XML tag and page format (extension).
	 *
	 * Lucinda Framework embeds a reporter for html format and another one for json. These are being used only if you are not defining 
	 * your own renderer via {format} subtag.
	 * 
	 * Supported syntax for XML tag above is:
	 * <rendering display_errors="{0 OR 1}">
	 * 		<{format} class="{CLASS}" folder="{FOLDER}" .../>
	 * </rendering>
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
		if(!$renderer || empty($renderer->$extension)) {
			switch($extension) {
				case "html":
					require_once("application/models/errors/renderers/HtmlRenderer.php");
					return new HtmlRenderer($showErrors, $this->application->getDefaultCharacterEncoding());
					break;
				case "json":
					require_once("application/models/errors/renderers/JsonRenderer.php");
					return new JsonRenderer($showErrors, $this->application->getDefaultCharacterEncoding());
					break;
				default:
					throw new ApplicationException("No default renderer defined for: ".$extension);					
					break;
			}
		} else {
			// use custom renderer per extension
			require_once("application/models/errors/renderers/CustomRenderer.php");
			$componentFinder = new ComponentFinder($renderer->$extension, "CustomRenderer", "errors.{environment}.renderer.{extension}");
			return $componentFinder->getComponent();
		}
	}
}