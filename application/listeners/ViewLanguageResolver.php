<?php
require_once("vendor/lucinda/view-language/loader.php");
require_once("vendor/lucinda/framework-engine/src/view_language/ViewLanguageBinder.php");

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
class ViewLanguageResolver extends Lucinda\MVC\STDOUT\ResponseListener {
    public function run() {
        if(strpos($this->response->headers()->get("Content-Type"),"text/html")!==0) return;
        new ViewLanguageBinder($this->application, $this->response);
	}
}