<?php

namespace Lucinda\Project\EventListeners;

use Lucinda\Internationalization\ConfigurationException;
use Lucinda\Internationalization\Wrapper;
use Lucinda\Framework\SingletonRepository;
use Lucinda\Project\Translator;
use Lucinda\STDOUT\EventListeners\Request;

require_once dirname(__DIR__, 2)."/helpers/translate.php";

/**
 * Sets up Internationalization API to use in automatic translation of response
 */
class Localization extends Request
{
    /**
     * {@inheritDoc}
     *
     * @throws ConfigurationException
     * @throws \Lucinda\MVC\ConfigurationException
     * @see    \Lucinda\MVC\Runnable::run()
     */
    public function run(): void
    {
        $wrapper = new Wrapper(
            $this->application->getXML(),
            $this->request->parameters(),
            $this->request->headers()
        );
        Translator::set($wrapper->getReader());
    }
}
