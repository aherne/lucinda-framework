<?php
require_once("vendor/lucinda/framework-engine/src/caching/CachingBinder.php");

/**
 * Binds STDOUT MVC with Http Caching API and contents of 'http_caching' tag @ configuration.xml 
 * in order to answer with just a 304 Not Modified header later on if view hasn't changed.
 */
class HttpCachingListener extends \Lucinda\MVC\STDOUT\ResponseListener {
    /**
     * {@inheritDoc}
     * @see \Lucinda\MVC\STDOUT\Runnable::run()
     */
    public function run() {
        if(strpos($this->response->headers()->get("Content-Type"),"text/html")!==0) return;
        new Lucinda\Framework\CachingBinder($this->application, $this->request, $this->response);
    }
}

