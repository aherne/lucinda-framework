<?php
require_once("libraries/php-view-language-api/loader.php");

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
		if($this->response->getContentType()!="text/html") return;
	  
		// get compilations folder
		$environment = $this->application->getAttribute("environment");
		$compilationsFolder = (string) $this->application->getXML()->application->paths->compilations->$environment;
		if(!$compilationsFolder) throw new ServletException("Compilations folder not defined!");
	  
		// compiles
		$vlp = new ViewLanguageParser(
				$this->application->getViewsPath(),
				$this->response->getView(),
				"php",
				"application/taglib");
		$strCompilationPath = $vlp->compile($compilationsFolder);
		 
		// disables error rendering
		$errorHandler = $this->application->getAttribute("error_handler");
		if($errorHandler) {
			$errorHandler->setRenderer(null);
		}

		// commits to output stream
		ob_start();
		$data = json_decode(json_encode($this->response->toArray()), true);
		require_once($strCompilationPath);
		$this->response->getOutputStream()->set(ob_get_contents());
		ob_end_clean();
	}
}