<?php
require_once("vendor/lucinda/framework-engine/src/view_language/ViewLanguageBinder.php");

/**
 * View resolver for HTML format binding STDOUT MVC with View Language API and contents of 'application' tag @ configuration.xml
 * in order to be able to perform templating in a view
 */
class ViewLanguageResolver extends \Lucinda\MVC\STDOUT\ViewResolver implements \Lucinda\MVC\STDERR\ErrorHandler
{
    /**
     * @var \Lucinda\MVC\STDERR\ErrorHandler
     */
    private $defaultErrorHandler;

    /**
     * {@inheritDoc}
     * @see \Lucinda\MVC\STDOUT\ViewResolver::getContent()
     */
    public function getContent()
    {
        $this->defaultErrorHandler = \Lucinda\MVC\STDERR\PHPException::getErrorHandler();

        // converts view language to PHP
        $wrapper = new Lucinda\Framework\ViewLanguageBinder($this->application->getTag("application"), $this->response->getView());
        $compilationFile = $wrapper->getCompilationFile();

        // take control of error reporting
        \Lucinda\MVC\STDERR\PHPException::setErrorHandler($this);
        set_exception_handler(array($this,"handle"));

        // compiles PHP file into output buffer
        $data = $this->response->attributes();
        ob_start();
        require_once($compilationFile);
        $output = ob_get_contents();
        ob_end_clean();

        // restore default error handler
        \Lucinda\MVC\STDERR\PHPException::setErrorHandler($this->defaultErrorHandler);
        set_exception_handler(array($this->defaultErrorHandler,"handle"));

        return $output;
    }

    /**
     * Handles errors by delegating to STDOUT MVC API
     *
     * @param \Exception $exception Encapsulates error information.
     */
    public function handle($exception)
    {
        // close output buffer
        ob_end_clean();

        // delegate handling to STDERR MVC API
        $this->defaultErrorHandler->handle($exception);
    }
}
