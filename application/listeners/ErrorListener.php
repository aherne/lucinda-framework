<?php
require_once("libraries/php-errors-api/loader.php");

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
	}
	
	/**
	 * Loads and returns error reporters based on contents of errors.{ENVIRONMENT}.reporting XML tag. 
	 * 
	 * Lucinda Framework embeds four reporters:
	 * - file: reporting is done in a file on your server's disk (the DEFAULT)
	 * - syslog: reporting is done via syslog service running on your server
	 * - sql: reporting is done into an sql database
	 * - nosql: reporting is done into a no-sql database (eg: couchbase)
	 * but also allows you to add your own reporter using "reporter" tag!
	 * 
	 * Supported syntax for XML tag above is:
	 * <reporting>
	 * 		<file path="{FILE_PATH}" rotation="{ROTATION_PATTERN}"/>
	 * 		<syslog application="{APPLICATION_NAME}"/>
	 * 		<sql table="{TABLE_NAME}" server="{SERVER_NAME}" rotation="{ROTATION_PATTERN}"/>
	 * 		<nosql parameter="{PARAMETER_NAME}" server="{SERVER_NAME}" rotation="{ROTATION_PATTERN}"/>
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
		$xml = $this->application->getXML()->errors->$environment;
		if(empty($xml) || empty($xml->reporters)) {
			// if no reporters were defined, use default ones
			require_once("src/loggers/FileLogger.php");
			$output[] = new FileLogger(self::DEFAULT_LOG_FILE);
			return $output;
		}
		$reporters = $xml->reporters;
		
		// append file reporting
		if($reporters->file) {
			require_once("libraries/php-logging-api/src/FileLogger.php");
			require_once("src/errors/reporters/DiskReporter.php");
			$filePath = (string) $reporters->file->path;
			if(!$filePath) {
				throw new ApplicationException("Property 'path' missing in configuration.xml tag: errors.{environment}.reporters.file!");
			}
			$output[] = new DiskReporter(new FileLogger($filePath, (string) $reporters->file->rotation));
		}
		
		// append syslog reporting
		if($reporters->syslog) {
			require_once("libraries/php-logging-api/src/SysLogger.php");
			require_once("src/errors/reporters/DiskReporter.php");
			require_once("application/models/ExceptionWrapper.php");
			$applicationName = (string) $reporters->syslog->application;
			if(!$applicationName) {
				throw new ApplicationException("Property 'application' missing in configuration.xml tag: errors.{environment}.reporters.syslog!");
			}
			$output[] = new DiskReporter(new SysLogger($applicationName), new ExceptionWrapper());
		}
		
		// append sql reporting
		if($reporters->sql) {
			require_once("src/errors/reporters/SQLReporter.php");
			$serverName = (string) $reporters->sql->server;
			if(!class_exists("SQLConnectionFactory")) {
				throw new ApplicationException("SQLDataSourceInjector listener has not ran!");
			}
			$output[] = new SQLReporter(
					(string) $reporters->sql->table,
					($serverName?SQLConnectionFactory::getInstance($serverName):SQLConnectionSingleton::getInstance()), 
					(string) $reporters->sql->rotation);
		}
		
		// append nosql reporting
		if($reporters->nosql) {
			require_once("src/errors/reporters/NoSQLReporter.php");
			$serverName = (string) $reporters->nosql->server;
			if(!class_exists("NoSQLConnectionFactory")) {
				throw new ApplicationException("NoSQLDataSourceInjector listener has not ran!");
			}
			$output[] = new NoSQLReporter(
					(string) $reporters->nosql->parameter,
					($serverName?NoSQLConnectionFactory::getInstance($serverName):NoSQLConnectionSingleton::getInstance()), 
					(string) $reporters->nosql->rotation);
		}
		
		// append custom reporter
		if($reporters->reporter) {
			require_once("src/errors/reporters/CustomReporter.php");
			$output[] = $this->getCustomComponent($reporters->reporter, "CustomReporter", "errors.{environment}.reporters.reporter");
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
	 * <rendering>
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
		$xml = $this->application->getXML()->errors->$environment;
		if(empty($xml) || empty($xml->renderer) ||  empty($xml->renderer->$extension)) {
			// TODO: use default renderer (per extension ?)
		} else {
			// use custom renderer per extension
			$xml = $xml->renderer->$extension;
			require_once("src/errors/renderers/CustomRenderer.php");
			$output[] = $this->getCustomComponent($xml, "CustomRenderer", "errors.{environment}.renderer.{extension}");	
		}
	}
	
	/**
	 * Loads and returns a custom user defined reporter/renderer from XML.
	 * 
	 * @param SimpleXMLElement $xml Content of custom tag
	 * @param string $parentClassName Class custom reporter/renderer must extend.
	 * @param string $tagName Path of custom tag.
	 * @throws ApplicationException If XML structure is invalid
	 * @return ErrorReporter|ErrorRenderer
	 */
	private function getCustomComponent(SimpleXMLElement $xml, $parentClassName, $tagName) {
		$folder = (string) $xml->folder;
		if(!$folder) {
			throw new ApplicationException("Property 'folder' missing in configuration.xml tag: ".$tagName."!");
		}
		$class = (string) $xml->class;
		if(!$class) {
			throw new ApplicationException("Property 'class' missing in configuration.xml tag: ".$tagName."!");
		}
		if(!file_exists($folder."/".$class.".php")) {
			throw new ApplicationException("File could not be located on disk: ".$folder."/".$class.".php"."!");
		}
		require_once($folder."/".$class.".php");
		if(!class_exists($class)) {
			throw new ApplicationException("Class not found: ".$class);
		}
		if(!is_subclass_of($class, $parentClassName)) {
			throw new ApplicationException($class." must be a subclass of ".$parentClassName."!");
		}
		return new $class($xml);
	}
}