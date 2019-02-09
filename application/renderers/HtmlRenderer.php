<?php
/**
 * STDERR MVC error renderer for HTML format.
 */
class HtmlRenderer implements \Lucinda\MVC\STDERR\ErrorRenderer
{
    /**
     * {@inheritDoc}
     * @see \Lucinda\MVC\STDERR\ErrorRenderer::render()
     */
    public function render(Lucinda\MVC\STDERR\Response $response) {
        $viewFile = $response->getView();
        if($viewFile && !$response->getBody()) {
            if(!file_exists($viewFile.".html")) throw new \Lucinda\MVC\STDERR\Exception("View file not found: ".$viewFile);
            ob_start();
            $_VIEW = $response->getAttributes();
            require_once($viewFile.".html");
            $output = ob_get_contents();
            ob_end_clean();
            $response->setBody($output);
        }
    }
}