<?php
/**
 * STDERR MVC controller that gets activated whenever an error occurs during application lifecycle. 
 * Class is open for modification if developers want to use templating on response body or support formats other than html and json.
 */
class ErrorsController extends \Lucinda\MVC\STDERR\Controller {
    /**
     * {@inheritDoc}
     * @see \Lucinda\MVC\STDERR\Controller::run()
     */
    public function run() {
        $this->setResponseStatus();
        $this->setResponseBody();
    }
    
    /**
     * Sets response status to HTTP status code 500
     */
    private function setResponseStatus() {
        $this->response->setStatus(500);
    }
    
    /**
     * Sets response body from view file or stream.
     * 
     * @throws Exception If content type of response is other than JSON or HTML. 
     */
    private function setResponseBody() {
        // gets whether or not errors should be displayed
        $displayErrors = $this->application->getDisplayErrors();
        
        // gets content type
        $contentType = $this->response->header("Content-Type");
        
        // sets view
        if(strpos($contentType, "text/html")==0) {
            if($displayErrors) {
                $exception = $this->request->getException();
                ob_start();
                require_once($this->application->getViewsPath()."/debug.php");
                $output = ob_get_contents();
                ob_end_clean();
                $this->response->getOutputStream()->write($output);
            } else {
                $this->response->setView($this->application->getViewsPath()."/500");
            }
        } else if(strpos($contentType, "application/json")==0) {
            $this->response->attributes("status", "error");
            $this->response->attributes("body", $displayErrors?$this->request->getException()->getMessage():"");
        } else {
            throw new Exception("Unsupported content type!");
        }
    }
}
