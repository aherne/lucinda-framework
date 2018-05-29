<?php
require_once("vendor/lucinda/errors/loader.php");

/**
 * Front controller over STDERR flow on top of ErrorHandler skeleton @ ERRORS API
 */
class ErrorsFrontController extends ErrorHandler
{
    /**
     * Sets up a handler, directs all STDERR flow to that handler and registers a default view renderer based on input
     *
     * @param string $displayFormat Extension that identifies how rendering format
     * @param string $displayCharset Charset to use when error is rendered.
     * @param boolean $displayErrors Whether or not rendered response should include error information (applies to bugs).
     */
    public function __construct($displayFormat="html", $displayCharset="UTF-8", $displayErrors=true)
    {
        $className = ucwords(strtolower($displayFormat))."Renderer";
        require_once("application/models/error_renderers/".$className.".php");
        $this->setRenderer(new $className($displayErrors, $displayCharset));
        PHPException::setErrorHandler($this);
        set_exception_handler(array($this,"handle"));
        ini_set("display_errors",0);
    }
}