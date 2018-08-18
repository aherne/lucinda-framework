<?php
class IndexController extends Controller {
    public function run() {
        $this->response->setView("index");
        $this->response->setAttribute("user", "member");
    }
}