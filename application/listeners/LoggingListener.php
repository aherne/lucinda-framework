<?php
require_once("application/models/getParentNode.php");

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
     * @see \Lucinda\MVC\Runnable::run()
     */
    public function run(): void
    {
        $wrapper = new Lucinda\Logging\Wrapper(getParentNode($this->application, "loggers"), ENVIRONMENT);
        $this->attributes->setLogger($wrapper->getLogger());
    }
}
