<?php
require_once("vendor/lucinda/http-caching/loader.php");
require_once("vendor/lucinda/framework-engine/src/caching/CachingRunner.php");

class HttpCachingListener extends ResponseListener {
    public function run() {
        if(strpos($this->response->headers()->get("Content-Type"),"text/html")!==0) return;
        new CachingBinder($this->application, $this->request, $this->response);
    }
}

