<?php
class PrivateController extends Controller {
    public function run() 
    {
        $this->response->setView("private");
    }
}