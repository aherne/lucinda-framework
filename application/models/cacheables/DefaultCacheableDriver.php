<?php
/**
 * Simple CacheableDriver that generates an ETAG based on response body & headers, request URL and XML settings (requires attribute 'secret' set in 'http_caching' tag). 
 */
class DefaultCacheableDriver extends Lucinda\Framework\CacheableDriver {
    /**
     * {@inheritDoc}
     * @see \Lucinda\Framework\CacheableDriver::setTime()
     */
    protected function setTime() {
        $this->last_modified_time = null;
    }
    
    /**
     * {@inheritDoc}
     * @see \Lucinda\Framework\CacheableDriver::setEtag()
     */
    protected function setEtag() {
        $secret = (string) $this->application->getTag("http_caching")["secret"];
        if(!$secret) throw new Lucinda\MVC\STDOUT\XMLException("Attribute 'secret' is required for 'http_caching' tag");
        $uri = $this->request->getServer()->getName()."/".$this->request->getURI()->getContextPath()."/".$this->request->getURI()->getPage()."?".$this->request->getURI()->getQueryString();
        $headers = $this->response->headers()->toArray();
        $outputStream = $this->response->getOutputStream()->get();
        $this->etag = sha1($secret."#".$uri."#".json_encode($headers)."#".$outputStream);
    }
}