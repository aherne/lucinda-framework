<?php
require_once(dirname(__DIR__, 4)."/vendor/lucinda/framework-engine/src/view_language/ViewLanguageBinder.php");

class ViewLanguageRenderer implements \Lucinda\MVC\STDERR\ErrorRenderer {
    /**
     * {@inheritDoc}
     * @see \Lucinda\MVC\STDERR\ErrorRenderer::render()
     */
    public function render(Lucinda\MVC\STDERR\Response $response) {
        if(!$response->getBody()) {
            // gets simplexml application object
            $application = simplexml_load_file(dirname(__DIR__, 4)."/stderr.xml")->application;
            
            // converts view language to PHP
            $wrapper = new Lucinda\Framework\ViewLanguageBinder($application, $this->response->getView());
            $compilationFile = $wrapper->getCompilationFile();
            
            // compiles PHP file into output buffer
            $data = $this->response->getAttributes();
            ob_start();
            require_once($compilationFile);
            $output = ob_get_contents();
            ob_end_clean();
            
            // saves stream
            $response->setBody($output);
        }
        $response->commit();
    }
}