<?php
/**
 * CacheableDriver that generates an ETAG based on host, response body & headers. 
 */
class EtagCacheableDriver extends Lucinda\Framework\CacheableDriver {
    /**
     * {@inheritDoc}
     * @see \Lucinda\Framework\CacheableDriver::setTime()
     */
    protected function setTime() {}
    
    /**
     * {@inheritDoc}
     * @see \Lucinda\Framework\CacheableDriver::setEtag()
     */
    protected function setEtag() {
        $this->etag = sha1($this->request->getServer()->getName()."#".json_encode($this->response->headers()->toArray())."#".$this->response->getOutputStream()->get());
    }
}