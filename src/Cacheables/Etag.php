<?php
namespace Lucinda\Project\Cacheables;

use Lucinda\Framework\AbstractCacheable;

/**
 * CacheableDriver that generates an ETAG based on host, response body & headers.
 */
class Etag extends AbstractCacheable
{
    /**
     * {@inheritDoc}
     * @see \Lucinda\Framework\AbstractCacheable::setTime()
     */
    protected function setTime(): void
    {
    }
    
    /**
     * {@inheritDoc}
     * @see \Lucinda\Framework\AbstractCacheable::setEtag()
     */
    protected function setEtag(): void
    {
        $this->etag = sha1($this->request->getServer()->getName()."#".$this->response->getBody());
    }
}
