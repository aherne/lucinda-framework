<?php

namespace Lucinda\Project\EventListeners;

use Lucinda\Logging\ConfigurationException;
use Lucinda\Logging\RequestInformation;
use Lucinda\STDOUT\EventListeners\Request as RequestListener;
use Lucinda\Project\Attributes;
use Lucinda\Logging\Wrapper;

/**
 * Sets up Logging API to use in logging later on
 */
class Logging extends RequestListener
{
    /**
     * @var Attributes
     */
    protected \Lucinda\STDOUT\Attributes $attributes;

    /**
     * {@inheritDoc}
     * @throws ConfigurationException
     * @see \Lucinda\MVC\Runnable::run()
     */
    public function run(): void
    {
        $requestInformation = new RequestInformation();
        $requestInformation->setUri($this->request->getURI()->getPage());
        $requestInformation->setUserAgent($this->request->headers("User-Agent"));
        $requestInformation->setIpAddress($this->request->getClient()->getIP());

        $wrapper = new Wrapper($this->application->getXML(), $requestInformation, ENVIRONMENT);
        $this->attributes->setLogger($wrapper->getLogger());
    }
}
