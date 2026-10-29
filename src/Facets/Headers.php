<?php

namespace Lucinda\Project\Facets;

use Lucinda\Headers\Wrapper;
use Lucinda\MVC\Facet;

/**
 * Exposes the current request's HTTP headers wrapper for constructor injection
 *
 * The same wrapper is shared with consumers so cache and CORS validation can
 * accumulate response headers on its encapsulated response.
 */
final class Headers implements Facet
{
    /**
     * HTTP headers policy, request headers, and accumulated response headers
     */
    private ?Wrapper $wrapper = null;

    /**
     * Encapsulates the headers wrapper prepared for the current request
     *
     * @param Wrapper $wrapper Initialized HTTP headers wrapper
     */
    public function setWrapper(Wrapper $wrapper)
    {
        $this->wrapper = $wrapper;
    }

    /**
     * Gets the shared wrapper used for cache and CORS validation
     *
     * @return ?Wrapper HTTP headers wrapper for the current request
     */
    public function getWrapper(): ?Wrapper
    {
        return $this->wrapper;
    }
}
