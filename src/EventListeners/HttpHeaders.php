<?php
namespace Lucinda\Project\EventListeners;

use Lucinda\Project\Attributes;
use Lucinda\Headers\Wrapper;
use Lucinda\STDOUT\EventListeners\Request;

require_once(dirname(__DIR__, 2)."/helpers/getParentNode.php");

/**
 * Sets up HTTP Headers API for later cache/cors validation or request/response headers operations
 */
class HttpHeaders extends Request
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
        $wrapper = new Wrapper(\getParentNode($this->application, "headers"), $this->attributes->getValidPage(), $this->request->headers());
        $this->attributes->setHeaders($wrapper);
    }
}
