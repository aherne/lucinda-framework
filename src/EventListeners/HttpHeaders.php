<?php

namespace Lucinda\Project\EventListeners;

use Lucinda\Project\Attributes;
use Lucinda\Headers\Wrapper;
use Lucinda\STDOUT\EventListeners\Request;

/**
 * Sets up HTTP Headers API for later cache/cors validation or request/response headers operations
 */
class HttpHeaders extends Request
{
    /**
     * @var Attributes
     */
    protected \Lucinda\STDOUT\Attributes $attributes;

    /**
     * {@inheritDoc}
     *
     * @throws \Lucinda\MVC\ConfigurationException
     * @see    \Lucinda\MVC\Runnable::run()
     */
    public function run(): void
    {
        $wrapper = new Wrapper(
            $this->application->getXML(),
            $this->attributes->getValidPage(),
            $this->request->headers()
        );
        $this->attributes->setHeaders($wrapper);
    }
}
