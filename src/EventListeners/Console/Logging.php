<?php

namespace Lucinda\Project\EventListeners\Console;

use Lucinda\ConsoleSTDOUT\EventListeners\Request as RequestListener;
use Lucinda\Logging\ConfigurationException;
use Lucinda\Logging\RequestInformation;
use Lucinda\Project\ConsoleAttributes;
use Lucinda\Logging\Wrapper;

/**
 * Sets up Logging API to use in logging later on
 */
class Logging extends RequestListener
{
    /**
     * @var ConsoleAttributes
     */
    protected \Lucinda\ConsoleSTDOUT\Attributes $attributes;

    /**
     * {@inheritDoc}
     *
     * @throws ConfigurationException
     * @see    \Lucinda\MVC\Runnable::run()
     */
    public function run(): void
    {
        $requestInformation = new RequestInformation();
        $requestInformation->setUri($this->request->getRoute());

        $wrapper = new Wrapper($this->application->getXML(), $requestInformation, ENVIRONMENT);
        $this->attributes->setLogger($wrapper->getLogger());
    }
}
