<?php

namespace Lucinda\Project\EventListeners;

use Lucinda\Logging\ConfigurationException;
use Lucinda\STDOUT\EventListeners\Application;
use Lucinda\Project\Attributes;
use Lucinda\Logging\Wrapper;

/**
 * Sets up Logging API to use in logging later on
 */
class Logging extends Application
{
    /**
     * @var Attributes
     */
    protected \Lucinda\STDOUT\Attributes $attributes;

    /**
     * {@inheritDoc}
     * @throws ConfigurationException
     * @throws \Lucinda\MVC\ConfigurationException
     * @see \Lucinda\MVC\Runnable::run()
     */
    public function run(): void
    {
        $wrapper = new Wrapper($this->application->getXML(), ENVIRONMENT);
        $this->attributes->setLogger($wrapper->getLogger());
    }
}
