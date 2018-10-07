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
        $headers = $this->response->headers()->toArray();
        $outputStream = $this->response->getOutputStream()->get();
        $this->etag = sha1(json_encode($headers)."#".$outputStream);
    }
}