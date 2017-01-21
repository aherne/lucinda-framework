<?php
/**
 * Response listener for PHP Servlets API that implements View Language logic.
 */
class DefaultResponseListener extends ResponseListener {
	public function run() {
	    if($this->response->getContentType()=="application/json") return;
	    
		// compile
		$vlp = new ViewLanguageParser($this->application->getViewsPath(), $this->request->getAttribute("page_url"), "php", "application/taglib");
		$strCompilationPath = $vlp->compile($this->application->getAttribute("compilations_folder"));

		// commit to output stream
		ob_start();
		$data = json_decode(json_encode($this->response->toArray()), true);
		require_once($strCompilationPath);
		$this->response->getOutputStream()->set(ob_get_contents());
		ob_end_clean();
	}
}