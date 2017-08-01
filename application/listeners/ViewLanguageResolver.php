<?php
require_once("libraries/php-view-language-api/loader.php");
require_once("src/Json.php");

/**
 * Performs view templating in your application by binding PHP-VIEW-LANGUAGE-API with SERVLETS-API. View language constructs found in views:
 * - expressions such as: 
 * 		Hello world, ${user}
 * - tags such as: 
 * 		<import file="header"/>
 * 		or
 * 		<standard:if condition="${user}=='Lucinda'">...</standard:if>
 * are parsed recursively and compiled into a PHP source, which is then executed in output buffer in order to save results in output stream. Theoretically,
 * one can have other ResponseListeners that will do yet more changes to output stream.
 * 
 * Because compilation process is very costly, old compilations are cached and not recalculated except when sources were changed. This whole process requires
 * presence of following attributes in configuration.xml:
 * - application.paths.compilations.{environment} : this is the path where compilations will be cached to (it must be writable!)
 * And also demands EnvironmentDetector having ran beforehand
 * 
 * Notice: error rendering (but not reporting) will be disabled before compilation is saved to output buffer, so we won't have mixed content in view. 
 */
class ViewLanguageResolver extends ResponseListener {
	public function run() {
		if(strpos($this->response->headers()->get("Content-Type"),"text/html")!==0) return;
	  
		// get compilations folder
		$environment = $this->application->getAttribute("environment");
		$compilationsFolder = (string) $this->application->getXML()->application->paths->compilations->$environment;
		if(!$compilationsFolder) throw new ServletException("Compilations folder not defined!");
		$tagsFolder = (string) $this->application->getXML()->application->paths->tags;
		$extension = (string) $this->application->getXML()->application->templates_extension;
		
		// compiles templates recursively into a single compilation file
		$vlp = new ViewLanguageParser($this->application->getViewsPath(), $extension, $compilationsFolder, $tagsFolder);
		$compilationFile = $vlp->compile($this->response->getView());
		
		// converts objects sent to response into array (throws JsonException if object is non-convertible)
		$json = new Json();
		$data = $json->decode($json->encode($this->response->toArray()));
		 
		// disables error rendering
		$errorHandler = $this->application->getAttribute("error_handler");
		if($errorHandler) {
			$errorHandler->setRenderer(null);
		}

		// commits response to output stream
		ob_start();
		require_once($compilationFile);
		$this->response->getOutputStream()->set(ob_get_contents());
		ob_end_clean();
	}
}