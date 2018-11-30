<?php
require_once("vendor/lucinda/framework-engine/src/view_language/ViewLanguageBinder.php");

/**
 * View resolver for HTML format binding STDOUT MVC with View Language API and contents of 'application' tag @ configuration.xml
 * in order to be able to perform templating in a view
 */
class ViewLanguageResolver extends Lucinda\MVC\STDOUT\ViewResolver {
    /**
     * {@inheritDoc}
     * @see \Lucinda\MVC\STDOUT\ViewResolver::getContent()
     */
    public function getContent() {
        // converts view language to PHP
        $wrapper = new Lucinda\Framework\ViewLanguageBinder($this->application->getTag("application"), $this->response->getView());
        $compilationFile = $wrapper->getCompilationFile();
        
        // compiles PHP file into output buffer
        $data = $this->response->attributes()->toArray();
        ob_start();
        require_once($compilationFile);
        $output = ob_get_contents();
        ob_end_clean();
        
        return $output;
	}
}