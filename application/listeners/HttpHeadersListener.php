<?php
/**
 * Sets up HTTP Headers API for later cache/cors validation or request/response headers operations
 */
class HttpHeadersListener extends \Lucinda\STDOUT\EventListeners\Request
{
    /**
     * @var Attributes
     */
    protected $attributes;
    
    /**
     * {@inheritDoc}
     * @see \Lucinda\STDOUT\Runnable::run()
     */
    public function run(): void
    {
        $wrapper = new Lucinda\Headers\Wrapper($this->application->getXML(), $this->attributes->getValidPage(), $this->request->headers());
        $this->attributes->setHeaders($wrapper);
    }
}
