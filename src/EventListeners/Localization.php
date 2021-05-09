<?php
namespace Lucinda\Project\EventListeners;

use Lucinda\UnitTest\Validator\URL\Request;
use Lucinda\Internationalization\Wrapper;
use Lucinda\Framework\SingletonRepository;

require_once(dirname(__DIR__, 2)."/helpers/translate.php");
require_once(dirname(__DIR__, 2)."/helpers/getParentNode.php");

/**
 * Sets up Internationalization API to use in automatic translation of response
 */
class Localization extends Request
{
    /**
     * {@inheritDoc}
     * @see \Lucinda\MVC\Runnable::run()
     */
    public function run(): void
    {
        $wrapper = new Wrapper(\getParentNode($this->application, "internationalization"), $this->request->parameters(), $this->request->headers());
        SingletonRepository::set("translations", $wrapper->getReader());
    }
}
