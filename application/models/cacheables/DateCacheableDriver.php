<?php
/**
 * CacheableDriver that generates a last modified time based on host, response body & headers. Useful when app is behind proxies that hide ETAGs.
 * Requires a NoSQL provider to save generated last modified time into. 
 */
class DateCacheableDriver extends \Lucinda\Framework\CacheableDriver {
    const EXPIRATION = 24*60*60;
    
    /**
     * {@inheritDoc}
     * @see \Lucinda\Framework\CacheableDriver::setTime()
     */
    protected function setTime() {
        // generates etag
        $etag = sha1($this->request->getServer()->getName()."#".json_encode($this->response->headers())."#".$this->response->getOutputStream()->get());
        if($etag) {
            $connection = Lucinda\NoSQL\ConnectionSingleton::getInstance();
            if($connection->contains($etag)) {
                $modifiedTime = $connection->get($etag);
                if(!$modifiedTime) {
                    $connection->delete($etag);
                }
            } else {
                $connection->set($etag, time(), self::EXPIRATION);
            }
        }
        return $modifiedTime;
    }
    
    /**
     * {@inheritDoc}
     * @see \Lucinda\Framework\CacheableDriver::setEtag()
     */
    protected function setEtag() {}
}