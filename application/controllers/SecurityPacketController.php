<?php
class SecurityPacketController  extends \Lucinda\MVC\STDERR\Controller {
    public function run() {
        $this->setResponseStatus();
        $this->setResponseBody();
    }
    
    private function setResponseStatus() {
        switch($this->request->getException()->getStatus()) {
            case "unauthorized":
                $this->response->setHttpStatus("401 Unauthorized");
                break;
            case "forbidden":
                $this->response->setHttpStatus("403 Forbidden");
                break;
            case "not_found":
                $this->response->setHttpStatus("404 Not found");
                break;
            default:
                break;
        }
    }
    
    private function setResponseBody() {
        // gets content type
        $contentType = $this->response->getHeader("Content-Type");
        
        // gets packet status
        $status = $this->request->getException()->getStatus();
        
        // gets wrapped exception
        $exception = $this->request->getException();
        
        // sets response content
        if(strpos($contentType, "text/html")==0) {
            switch($status) {
                case "unauthorized":
                    $this->response->setView($this->application->getViewsPath()."/401");
                    break;
                case "forbidden":
                    $this->response->setView($this->application->getViewsPath()."/403");
                    break;
                case "not_found":
                    $this->response->setView($this->application->getViewsPath()."/404");
                    break;
                default:
                    $this->redirect($exception);
                    break;
            }
        } else if(strpos($contentType, "application/json")==0) {
            switch($status) {
                case "unauthorized":
                    $this->response->setBody(array("status"=>"error","body"=>"", "callback"=>$exception->getCallback()));
                    break;
                case "forbidden":
                    $this->response->setBody(array("status"=>"error","body"=>"", "callback"=>$exception->getCallback()));
                    break;
                case "not_found":
                    $this->response->setBody(array("status"=>"error","body"=>"", "callback"=>$exception->getCallback()));
                    break;
                case "login_ok":
                    $this->response->setBody(array("status"=>"login_ok","body"=>"", "callback"=>$exception->getCallback(), "token"=>$exception->getAccessToken()));
                    break;
                default:
                    $this->response->setBody(array("status"=>$exception->getStatus(), "body"=>"", "callback"=>$exception->getCallback()));
                    break;
            }
        } else {
            throw new Exception("Unsupported content type!");
        }
    }
    
    
    private function redirect(Lucinda\Framework\SecurityPacket $exception) {
        $location = $exception->getCallback().($exception->getStatus()!="redirect"?"?status=".$exception->getStatus():"");
        
        header("Cache-Control: no-cache, no-store, must-revalidate"); // HTTP 1.1.
        header("Pragma: no-cache");
        header("Expires: 0");
        header('Location: '.$location, true, 302);
        exit();
    }
}