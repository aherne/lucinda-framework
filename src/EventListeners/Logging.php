<?php
namespace Lucinda\Project\EventListeners;

use Lucinda\STDOUT\EventListeners\Application;
use Lucinda\Project\Attributes;
use Lucinda\Logging\Wrapper;

require_once(dirname(__DIR__, 2)."/helpers/getParentNode.php");

/**
 * Sets up Logging API to use in logging later on
 */
class Logging extends Application
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
        $wrapper = new Wrapper(\getParentNode($this->application, "loggers"), ENVIRONMENT);
        $this->attributes->setLogger($wrapper->getLogger());
    }
}
