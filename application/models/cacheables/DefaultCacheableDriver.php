<?php
class DefaultCacheableDriver extends CacheableDriver {
    protected function setTime() {
        return null;
    }
    
    protected function setEtag() {
        $secret = $this->application->getXML()->http_caching["secret"];
        if(!$secret) throw new ApplicationException("Attribute secret missing in http_caching XML tag");
        $uri = $this->request->getServer()->getName()."/".$this->request->getURI()->getContextPath()."/".$this->request->getURI()->getPage()."?".$this->request->getURI()->getQueryString();
        $headers = $this->response->headers()->toArray();
        $outputStream = $this->response->getOutputStream()->get();
        return sha1($secret."#".$uri."#".json_encode($headers)."#".$outputStream);
    }
}