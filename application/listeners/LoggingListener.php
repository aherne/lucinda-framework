<?php
/**
 * Sets up Logging API to use in logging later on
 */
class LoggingListener extends \Lucinda\STDOUT\EventListeners\Application
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
        $wrapper = new Lucinda\Logging\Wrapper($this->application->getXML(), ENVIRONMENT);
        $this->attributes->setLogger($wrapper->getLogger());
    }
}
