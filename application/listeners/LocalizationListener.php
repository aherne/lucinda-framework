<?php
require("application/models/translate.php");

/**
 * Sets up Internationalization API to use in automatic translation of response
 */
class LocalizationListener extends \Lucinda\STDOUT\EventListeners\Request
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
        $wrapper = new Lucinda\Internationalization\Wrapper($this->application->getXML(), $this->request->parameters(), $this->request->headers());
        \Lucinda\Framework\SingletonRepository::set("translations", $wrapper->getReader());
    }
}
