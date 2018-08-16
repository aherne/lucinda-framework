<?php
class IndexController extends Controller {
    public function run() {
        $this->response->setAttribute("head", "x");
        $this->response->setAttribute("body", "y");
        $this->response->setAttribute("foot", "z");
        $this->response->setView("index");
    }
}