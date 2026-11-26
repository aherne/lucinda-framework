<?php

namespace Lucinda\Project\Cacheables;

use Lucinda\Framework\AbstractCacheable;

/**
 * Computes an entity tag from the request host and rendered response bytes
 *
 * Construction uses the inherited request and body snapshot. Repeated
 * representations retain their tag; no Last-Modified timestamp is supplied.
 */
final class Etag extends AbstractCacheable
{
    /**
     * Leaves Last-Modified unset for this ETag-only cache policy
     *
     * @return void
     */
    protected function setTime(): void
    {
    }

    /**
     * Computes the validator from the host and complete response bytes
     *
     * @return void
     */
    protected function setEtag(): void
    {
        $this->etag = sha1(
            $this->request->getServer()->getName() . "#" . $this->response
        );
    }
}
