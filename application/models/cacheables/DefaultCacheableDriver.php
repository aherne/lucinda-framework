<?php
class DefaultCacheableDriver extends Lucinda\Framework\CacheableDriver {
    protected function setTime() {
        $this->last_modified_time = null;
    }
    
    protected function setEtag() {
        $secret = (string) $this->application->getTag("http_caching")["secret"];
        if(!$secret) throw new Lucinda\MVC\STDOUT\XMLException("Attribute secret missing in http_caching XML tag");
        $uri = $this->request->getServer()->getName()."/".$this->request->getURI()->getContextPath()."/".$this->request->getURI()->getPage()."?".$this->request->getURI()->getQueryString();
        $headers = $this->response->headers()->toArray();
        $outputStream = $this->response->getOutputStream()->get();
        $this->etag = sha1($secret."#".$uri."#".json_encode($headers)."#".$outputStream);
    }
}