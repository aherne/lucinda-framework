<?php
require_once("libraries/php-view-language-api/loader.php");

/**
 * Response listener for PHP Servlets API that implements View Language logic.
 */
class ViewLanguageResolver extends ResponseListener {
	public function run() {
	    if($this->response->getContentType()!="text/html") return;
	    
	    // get compilations folder
	    $environment = $this->application->getAttribute("environment");
	    $compilationsFolder = (string) $this->application->getXML()->application->paths->compilations->$environment;
	    if(!$compilationsFolder) throw new ServletException("Compilations folder not defined!");
	    
		// compile
		$vlp = new ViewLanguageParser(
				$this->application->getViewsPath(), 
				$this->response->getView(), 
				"html", 
				"application/taglib");
		$strCompilationPath = $vlp->compile($compilationsFolder);

		// commit to output stream
		ob_start();
		$data = json_decode(json_encode($this->response->toArray()), true);
		require_once($strCompilationPath);
		$this->response->getOutputStream()->set(ob_get_contents());
		ob_end_clean();
	}
}