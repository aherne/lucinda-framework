<?php
require_once(dirname(dirname(__DIR__))."/vendor/lucinda/framework-engine/src/view_language/ViewLanguageBinder.php");

/**
 * STDERR MVC error renderer for HTML format using ViewLanguage templating.
 */
class ViewLanguageRenderer implements \Lucinda\MVC\STDERR\ErrorRenderer {
    /**
     * {@inheritDoc}
     * @see \Lucinda\MVC\STDERR\ErrorRenderer::render()
     */
    public function render(Lucinda\MVC\STDERR\Response $response) {
        if($response->getOutputStream()->isEmpty()) {
            // gets simplexml application object
            $application = simplexml_load_file(dirname(dirname(__DIR__))."/stderr.xml")->application;
            
            // gets view file
            $viewFile = $response->getView();
            $viewsPath = (string) $application->paths->views;
            $viewFile = substr($viewFile, strpos($viewFile, $viewsPath)+strlen($viewsPath)+1);
            
            // converts view language to PHP
            $wrapper = new Lucinda\Framework\ViewLanguageBinder($application, $viewFile);
            $compilationFile = $wrapper->getCompilationFile();
            
            // compiles PHP file into output buffer
            $data = $response->attributes();
            ob_start();
            require_once($compilationFile);
            $output = ob_get_contents();
            ob_end_clean();
            
            // saves stream
            $response->getOutputStream()->write($output);
        }
    }
}