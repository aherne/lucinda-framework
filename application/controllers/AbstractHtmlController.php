<?php
require_once("application/models/dao/Messages.php");

abstract class AbstractHtmlController extends Controller {   
    protected $statusCode;
    
    public function run() {
        $this->init();
        $this->service();
        $this->destroy();
    }
    
    // performs page-generic controller logic before page-specific controller logic is ran.
    protected function init() {
        $this->statusCode = (!empty($_GET["status"])?$_GET["status"]:"");
        
        $this->setView();
    }
    
    protected function setView() {
        $this->response->setView($this->application->getViewsPath()."/".$this->request->getAttribute("page_url"));
        $this->response->setAttribute("view", $this->request->getAttribute("page_url"));
    }
    
    // performs page-specific controller logic
    abstract protected function service();
    
    // performs page-generic controller logic after page-specific controller logic is ran.
    protected function destroy() {
        $this->setStatus();  
        // write to stream to bypass DefaultViewWrapper
        $this->response->getOutputStream()->write("OK");
    }
    
    protected function setStatus() {
        $object = new Messages();
        $info = $object->getInfo($this->statusCode);
        $this->response->setAttribute("status", $info);
    }
}