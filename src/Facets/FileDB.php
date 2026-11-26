<?php

namespace Lucinda\Project\Facets;

use Lucinda\DB\Wrapper;
use Lucinda\MVC\Facet;

/**
 * Publishes the configured file database wrapper for request consumers
 *
 * The file database listener must assign a wrapper before consumers retrieve
 * it. The facet shares the same wrapper and its configured collections.
 */
final class FileDB implements Facet
{
    /**
     * Initialized file database wrapper shared with request consumers
     */
    private Wrapper $wrapper;

    /**
     * Sets the file database wrapper prepared by its listener
     *
     * @param Wrapper $wrapper Initialized database configuration and
     * collections
     * @return void
     */
    public function setWrapper(Wrapper $wrapper): void
    {
        $this->wrapper = $wrapper;
    }

    /**
     * Gets the shared file database wrapper after initialization
     *
     * @return Wrapper Wrapper assigned by the file database listener
     * @throws \Error If no wrapper has been assigned
     */
    public function getWrapper(): Wrapper
    {
        return $this->wrapper;
    }
}
