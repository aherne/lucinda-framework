<?php

namespace Lucinda\Project\EventListeners\Console;

use Lucinda\ConsoleSTDOUT\EventListeners\Application;
use Lucinda\Logging\ConfigurationException;
use Lucinda\Project\ConsoleAttributes;
use Lucinda\Logging\Wrapper;

/**
 * Sets up Logging API to use in logging later on
 */
class Logging extends Application
{
    /**
     * @var ConsoleAttributes
     */
    protected \Lucinda\ConsoleSTDOUT\Attributes $attributes;

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
