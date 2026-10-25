<?php

namespace Lucinda\Project\Cacheables;

use Lucinda\Framework\AbstractCacheable;

/**
 * CacheableDriver that generates an ETAG based on host, response body & headers.
 */
class Date extends AbstractCacheable
{
    public const DRIVER_NAME = "";
    public const EXPIRATION = 24*60*60;

    /**
     * {@inheritDoc}
     *
     * @see \Lucinda\Framework\AbstractCacheable::setTime()
     */
    protected function setTime(): void
    {
        // generates etag
        $etag = sha1($this->request->getServer()->getName()."#".$this->response->getBody());
        $connection = \NoSQL(self::DRIVER_NAME);

        $modifiedTime = 0;
        if ($connection->contains($etag)) {
            $modifiedTime = $connection->get($etag);
            if (!$modifiedTime) {
                $connection->delete($etag);
            }
        } else {
            $modifiedTime = time();
            $connection->set($etag, $modifiedTime, self::EXPIRATION);
        }

        $this->lastModifiedTime =  $modifiedTime;
    }

    /**
     * {@inheritDoc}
     *
     * @see \Lucinda\Framework\AbstractCacheable::setEtag()
     */
    protected function setEtag(): void
    {
    }
}
