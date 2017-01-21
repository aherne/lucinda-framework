<?php
/**
 * Performs unified error display and reporting.
 */
class GeneralErrorReporting extends ErrorReporting {
    /**
     * {@inheritDoc}
     * @see ErrorReporting::store()
     */
    protected function store() {
        // load them here, instead of with class
        require_once(__DIR__."/DB.php");
        require_once(__DIR__."/dao/Bugs.php");
        
        $be = new BugEnvironment();
        $be->files = (!empty($_FILES)?$_FILES:array());
        $be->get = (!empty($_GET)?$_GET:array());
        $be->post = (!empty($POST)?$POST:array());
        $be->server = (!empty($_SERVER)?$_SERVER:array());
        
        $bug = new Bug();
        $bug->environment = $be;
        $bug->exception = $this->exception;
        
        try {
            $bugs = new Bugs();
            $bugs->save($bug);   
        } catch(Exception $e) {
            var_dump($e);
            die();
        }        
    }

    /**
     * {@inheritDoc}
     * @see ErrorReporting::display()
     */
    protected function display() {
        if(strpos($_SERVER["REQUEST_URI"],".json")!==false) {
            header("Content-Type: application/json");
            echo json_encode(array("status"=>"error","body"=>$this->exception->getMessage()));
        } else {
            header("Content-Type: text/html");
            echo $this->exception->getMessage();
        }
    }
}