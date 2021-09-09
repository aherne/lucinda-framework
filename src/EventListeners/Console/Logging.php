<?php
namespace Lucinda\Project\EventListeners\Console;

use Lucinda\ConsoleSTDOUT\EventListeners\Application;
use Lucinda\Project\ConsoleAttributes;
use Lucinda\Logging\Wrapper;

require_once(dirname(__DIR__, 3)."/helpers/getParentNode.php");

/**
 * Sets up Logging API to use in logging later on
 */
class Logging extends Application
{
    /**
     * @var ConsoleAttributes
     */
    protected $attributes;

    /**
     * {@inheritDoc}
     * @see \Lucinda\MVC\Runnable::run()
     */
    public function run(): void
    {
        $wrapper = new Wrapper(\getParentNode($this->application, "loggers"), ENVIRONMENT);
        $this->attributes->setLogger($wrapper->getLogger());
    }
}
