<?php
require_once("vendor/lucinda/framework-engine/src/view_language/ViewLanguageBinder.php");

/**
 * Binds STDOUT MVC with View Language API and contents of 'application' tag @ configuration.xml
 * in order to be able to perform templating in a view. Saves compilation result into output stream ready to be displayed later on.
 */
class ViewLanguageResolver extends Lucinda\MVC\STDOUT\ResponseListener {
    /**
     * {@inheritDoc}
     * @see Lucinda\MVC\STDOUT\Runnable::run()
     */
    public function run() {
        if(strpos($this->response->headers()->get("Content-Type"),"text/html")!==0) return;
        new Lucinda\Framework\ViewLanguageBinder($this->application, $this->response);
	}
}