<?php
class ErrorsController extends \Lucinda\MVC\STDERR\Controller {
    public function run() {
        $this->setResponseStatus();
        $this->setResponseBody();
    }
    
    private function setResponseStatus() {
        $this->response->setHttpStatus("500 Internal Server Error");
    }
    
    private function setResponseBody() {
        // gets whether or not errors should be displayed
        $developmentEnvironment = getenv("ENVIRONMENT");
        $displayErrors = (string) $this->application->getXML()->display_errors->{$developmentEnvironment};
        
        // gets content type
        $contentType = $this->response->getHeader("Content-Type");
        
        // sets view
        if(strpos($contentType, "text/html")==0) {
            if($displayErrors) {
                $exception = $this->request->getException();
                ob_start();
                require_once($this->application->getViewsPath()."/debug.php");
                $output = ob_get_contents();
                ob_end_clean();
                $this->response->setBody($output);
            } else {
                $this->response->setView($this->application->getViewsPath()."/500");
            }
        } else if(strpos($contentType, "application/json")==0) {
            if($displayErrors) {
                $this->response->setBody(array("status"=>"error", "body"=>$exception->getMessage()));
            }
        } else {
            throw new Exception("Unsupported content type!");
        }
    }
}